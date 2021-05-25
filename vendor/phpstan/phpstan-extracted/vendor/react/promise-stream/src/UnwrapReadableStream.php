<?php

namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\Stream;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Evenement\EventEmitter;
use InvalidArgumentException;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\PromiseInterface;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\Util;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface;
/**
 * @internal
 * @see unwrapReadable() instead
 */
class UnwrapReadableStream extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Evenement\EventEmitter implements \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface
{
    private $promise;
    private $closed = \false;
    /**
     * Instantiate new unwrapped readable stream for given `Promise` which resolves with a `ReadableStreamInterface`.
     *
     * @param PromiseInterface $promise Promise<ReadableStreamInterface, Exception>
     */
    public function __construct(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\PromiseInterface $promise)
    {
        $out = $this;
        $closed =& $this->closed;
        $this->promise = $promise->then(function ($stream) {
            if (!$stream instanceof \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface) {
                throw new \InvalidArgumentException('Not a readable stream');
            }
            return $stream;
        })->then(function (\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface $stream) use($out, &$closed) {
            // stream is already closed, make sure to close output stream
            if (!$stream->isReadable()) {
                $out->close();
                return $stream;
            }
            // resolves but output is already closed, make sure to close stream silently
            if ($closed) {
                $stream->close();
                return $stream;
            }
            // stream any writes into output stream
            $stream->on('data', function ($data) use($out) {
                $out->emit('data', array($data, $out));
            });
            // forward end events and close
            $stream->on('end', function () use($out, &$closed) {
                if (!$closed) {
                    $out->emit('end', array($out));
                    $out->close();
                }
            });
            // error events cancel output stream
            $stream->on('error', function ($error) use($out) {
                $out->emit('error', array($error, $out));
                $out->close();
            });
            // close both streams once either side closes
            $stream->on('close', array($out, 'close'));
            $out->on('close', array($stream, 'close'));
            return $stream;
        }, function ($e) use($out, &$closed) {
            if (!$closed) {
                $out->emit('error', array($e, $out));
                $out->close();
            }
            // resume() and pause() may attach to this promise, so ensure we actually reject here
            throw $e;
        });
    }
    public function isReadable()
    {
        return !$this->closed;
    }
    public function pause()
    {
        if ($this->promise !== null) {
            $this->promise->then(function (\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface $stream) {
                $stream->pause();
            });
        }
    }
    public function resume()
    {
        if ($this->promise !== null) {
            $this->promise->then(function (\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface $stream) {
                $stream->resume();
            });
        }
    }
    public function pipe(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface $dest, array $options = array())
    {
        \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Stream\Util::pipe($this, $dest, $options);
        return $dest;
    }
    public function close()
    {
        if ($this->closed) {
            return;
        }
        $this->closed = \true;
        // try to cancel promise once the stream closes
        if ($this->promise instanceof \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface) {
            $this->promise->cancel();
        }
        $this->promise = null;
        $this->emit('close');
        $this->removeAllListeners();
    }
}
