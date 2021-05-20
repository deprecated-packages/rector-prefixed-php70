<?php

namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Http\Message;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Http\Io\HttpBodyStream;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Response as Psr7Response;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\StreamInterface;
/**
 * Represents an outgoing server response message.
 *
 * ```php
 * $response = new React\Http\Message\Response(
 *     200,
 *     array(
 *         'Content-Type' => 'text/html'
 *     ),
 *     "<html>Hello world!</html>\n"
 * );
 * ```
 *
 * This class implements the
 * [PSR-7 `ResponseInterface`](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface)
 * which in turn extends the
 * [PSR-7 `MessageInterface`](https://www.php-fig.org/psr/psr-7/#31-psrhttpmessagemessageinterface).
 *
 * > Internally, this implementation builds on top of an existing incoming
 *   response message and only adds required streaming support. This base class is
 *   considered an implementation detail that may change in the future.
 *
 * @see \Psr\Http\Message\ResponseInterface
 */
final class Response extends \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\Response
{
    /**
     * @param int                                            $status  HTTP status code (e.g. 200/404)
     * @param array<string,string|string[]>                  $headers additional response headers
     * @param string|ReadableStreamInterface|StreamInterface $body    response body
     * @param string                                         $version HTTP protocol version (e.g. 1.1/1.0)
     * @param ?string                                        $reason  custom HTTP response phrase
     * @throws \InvalidArgumentException for an invalid body
     */
    public function __construct($status = 200, array $headers = array(), $body = '', $version = '1.1', $reason = null)
    {
        if ($body instanceof \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface && !$body instanceof \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\StreamInterface) {
            $body = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Http\Io\HttpBodyStream($body, null);
        } elseif (!\is_string($body) && !$body instanceof \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\StreamInterface) {
            throw new \InvalidArgumentException('Invalid response body given');
        }
        parent::__construct($status, $headers, $body, $version, $reason);
    }
}
