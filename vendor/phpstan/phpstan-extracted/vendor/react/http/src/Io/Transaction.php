<?php

namespace RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Io;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\UriInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Request;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Uri;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Message\ResponseException;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\PromiseInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface;
/**
 * @internal
 */
class Transaction
{
    private $sender;
    private $loop;
    // context: http.timeout (ini_get('default_socket_timeout'): 60)
    private $timeout;
    // context: http.follow_location (true)
    private $followRedirects = \true;
    // context: http.max_redirects (10)
    private $maxRedirects = 10;
    // context: http.ignore_errors (false)
    private $obeySuccessCode = \true;
    private $streaming = \false;
    private $maximumSize = 16777216;
    // 16 MiB = 2^24 bytes
    public function __construct(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Io\Sender $sender, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface $loop)
    {
        $this->sender = $sender;
        $this->loop = $loop;
    }
    /**
     * @param array $options
     * @return self returns new instance, without modifying existing instance
     */
    public function withOptions(array $options)
    {
        $transaction = clone $this;
        foreach ($options as $name => $value) {
            if (\property_exists($transaction, $name)) {
                // restore default value if null is given
                if ($value === null) {
                    $default = new self($this->sender, $this->loop);
                    $value = $default->{$name};
                }
                $transaction->{$name} = $value;
            }
        }
        return $transaction;
    }
    public function send(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface $request)
    {
        $deferred = new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred(function () use(&$deferred) {
            if (isset($deferred->pending)) {
                $deferred->pending->cancel();
                unset($deferred->pending);
            }
        });
        $deferred->numRequests = 0;
        // use timeout from options or default to PHP's default_socket_timeout (60)
        $timeout = (float) ($this->timeout !== null ? $this->timeout : \ini_get("default_socket_timeout"));
        $loop = $this->loop;
        $this->next($request, $deferred)->then(function (\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface $response) use($deferred, $loop, &$timeout) {
            if (isset($deferred->timeout)) {
                $loop->cancelTimer($deferred->timeout);
                unset($deferred->timeout);
            }
            $timeout = -1;
            $deferred->resolve($response);
        }, function ($e) use($deferred, $loop, &$timeout) {
            if (isset($deferred->timeout)) {
                $loop->cancelTimer($deferred->timeout);
                unset($deferred->timeout);
            }
            $timeout = -1;
            $deferred->reject($e);
        });
        if ($timeout < 0) {
            return $deferred->promise();
        }
        $body = $request->getBody();
        if ($body instanceof \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface && $body->isReadable()) {
            $that = $this;
            $body->on('close', function () use($that, $deferred, &$timeout) {
                if ($timeout >= 0) {
                    $that->applyTimeout($deferred, $timeout);
                }
            });
        } else {
            $this->applyTimeout($deferred, $timeout);
        }
        return $deferred->promise();
    }
    /**
     * @internal
     * @param Deferred $deferred
     * @param number  $timeout
     * @return void
     */
    public function applyTimeout(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred $deferred, $timeout)
    {
        $deferred->timeout = $this->loop->addTimer($timeout, function () use($timeout, $deferred) {
            $deferred->reject(new \RuntimeException('Request timed out after ' . $timeout . ' seconds'));
            if (isset($deferred->pending)) {
                $deferred->pending->cancel();
                unset($deferred->pending);
            }
        });
    }
    private function next(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface $request, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred $deferred)
    {
        $this->progress('request', array($request));
        $that = $this;
        ++$deferred->numRequests;
        $promise = $this->sender->send($request);
        if (!$this->streaming) {
            $promise = $promise->then(function ($response) use($deferred, $that) {
                return $that->bufferResponse($response, $deferred);
            });
        }
        $deferred->pending = $promise;
        return $promise->then(function (\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface $response) use($request, $that, $deferred) {
            return $that->onResponse($response, $request, $deferred);
        });
    }
    /**
     * @internal
     * @param ResponseInterface $response
     * @return PromiseInterface Promise<ResponseInterface, Exception>
     */
    public function bufferResponse(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface $response, $deferred)
    {
        $stream = $response->getBody();
        $size = $stream->getSize();
        if ($size !== null && $size > $this->maximumSize) {
            $stream->close();
            return \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\reject(new \OverflowException('Response body size of ' . $size . ' bytes exceeds maximum of ' . $this->maximumSize . ' bytes', \defined('SOCKET_EMSGSIZE') ? \SOCKET_EMSGSIZE : 0));
        }
        // body is not streaming => already buffered
        if (!$stream instanceof \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface) {
            return \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\resolve($response);
        }
        // buffer stream and resolve with buffered body
        $maximumSize = $this->maximumSize;
        $promise = \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Stream\buffer($stream, $maximumSize)->then(function ($body) use($response) {
            return $response->withBody(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\stream_for($body));
        }, function ($e) use($stream, $maximumSize) {
            // try to close stream if buffering fails (or is cancelled)
            $stream->close();
            if ($e instanceof \OverflowException) {
                $e = new \OverflowException('Response body size exceeds maximum of ' . $maximumSize . ' bytes', \defined('SOCKET_EMSGSIZE') ? \SOCKET_EMSGSIZE : 0);
            }
            throw $e;
        });
        $deferred->pending = $promise;
        return $promise;
    }
    /**
     * @internal
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @throws ResponseException
     * @return ResponseInterface|PromiseInterface
     */
    public function onResponse(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface $response, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface $request, $deferred)
    {
        $this->progress('response', array($response, $request));
        // follow 3xx (Redirection) response status codes if Location header is present and not explicitly disabled
        // @link https://tools.ietf.org/html/rfc7231#section-6.4
        if ($this->followRedirects && ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) && $response->hasHeader('Location')) {
            return $this->onResponseRedirect($response, $request, $deferred);
        }
        // only status codes 200-399 are considered to be valid, reject otherwise
        if ($this->obeySuccessCode && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400)) {
            throw new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Message\ResponseException($response);
        }
        // resolve our initial promise
        return $response;
    }
    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @return PromiseInterface
     * @throws \RuntimeException
     */
    private function onResponseRedirect(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface $response, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface $request, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred $deferred)
    {
        // resolve location relative to last request URI
        $location = \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Uri::resolve($request->getUri(), $response->getHeaderLine('Location'));
        $request = $this->makeRedirectRequest($request, $location);
        $this->progress('redirect', array($request));
        if ($deferred->numRequests >= $this->maxRedirects) {
            throw new \RuntimeException('Maximum number of redirects (' . $this->maxRedirects . ') exceeded');
        }
        return $this->next($request, $deferred);
    }
    /**
     * @param RequestInterface $request
     * @param UriInterface $location
     * @return RequestInterface
     */
    private function makeRedirectRequest(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface $request, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\UriInterface $location)
    {
        $originalHost = $request->getUri()->getHost();
        $request = $request->withoutHeader('Host')->withoutHeader('Content-Type')->withoutHeader('Content-Length');
        // Remove authorization if changing hostnames (but not if just changing ports or protocols).
        if ($location->getHost() !== $originalHost) {
            $request = $request->withoutHeader('Authorization');
        }
        // naïve approach..
        $method = $request->getMethod() === 'HEAD' ? 'HEAD' : 'GET';
        return new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Request($method, $location, $request->getHeaders());
    }
    private function progress($name, array $args = array())
    {
        return;
        echo $name;
        foreach ($args as $arg) {
            echo ' ';
            if ($arg instanceof \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\ResponseInterface) {
                echo 'HTTP/' . $arg->getProtocolVersion() . ' ' . $arg->getStatusCode() . ' ' . $arg->getReasonPhrase();
            } elseif ($arg instanceof \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\RequestInterface) {
                echo $arg->getMethod() . ' ' . $arg->getRequestTarget() . ' HTTP/' . $arg->getProtocolVersion();
            } else {
                echo $arg;
            }
        }
        echo \PHP_EOL;
    }
}
