<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Http\Io;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Evenement\EventEmitter;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\ReadableStreamInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\Util;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\WritableStreamInterface;
/**
 * [Internal] Bridge between StreamInterface from PSR-7 and ReadableStreamInterface from ReactPHP
 *
 * This class is used in the server to stream the body of an incoming response
 * from the client. This allows us to stream big amounts of data without having
 * to buffer this data. Similarly, this used to stream the body of an outgoing
 * request body to the client. The data will be sent directly to the client.
 *
 * Note that this is an internal class only and nothing you should usually care
 * about. See the `StreamInterface` and `ReadableStreamInterface` for more
 * details.
 *
 * @see StreamInterface
 * @see ReadableStreamInterface
 * @internal
 */
class HttpBodyStream extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Evenement\EventEmitter implements \RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\ReadableStreamInterface
{
    public $input;
    private $closed = \false;
    private $size;
    /**
     * @param ReadableStreamInterface $input Stream data from $stream as a body of a PSR-7 object4
     * @param int|null $size size of the data body
     */
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\ReadableStreamInterface $input, $size)
    {
        $this->input = $input;
        $this->size = $size;
        $this->input->on('data', array($this, 'handleData'));
        $this->input->on('end', array($this, 'handleEnd'));
        $this->input->on('error', array($this, 'handleError'));
        $this->input->on('close', array($this, 'close'));
    }
    public function isReadable()
    {
        return !$this->closed && $this->input->isReadable();
    }
    public function pause()
    {
        $this->input->pause();
    }
    public function resume()
    {
        $this->input->resume();
    }
    public function pipe(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\WritableStreamInterface $dest, array $options = array())
    {
        \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Stream\Util::pipe($this, $dest, $options);
        return $dest;
    }
    public function close()
    {
        if ($this->closed) {
            return;
        }
        $this->closed = \true;
        $this->input->close();
        $this->emit('close');
        $this->removeAllListeners();
    }
    public function getSize()
    {
        return $this->size;
    }
    /** @ignore */
    public function __toString()
    {
        return '';
    }
    /** @ignore */
    public function detach()
    {
        return null;
    }
    /** @ignore */
    public function tell()
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function eof()
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function isSeekable()
    {
        return \false;
    }
    /** @ignore */
    public function seek($offset, $whence = \SEEK_SET)
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function rewind()
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function isWritable()
    {
        return \false;
    }
    /** @ignore */
    public function write($string)
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function read($length)
    {
        throw new \BadMethodCallException();
    }
    /** @ignore */
    public function getContents()
    {
        return '';
    }
    /** @ignore */
    public function getMetadata($key = null)
    {
        return null;
    }
    /** @internal */
    public function handleData($data)
    {
        $this->emit('data', array($data));
    }
    /** @internal */
    public function handleError(\Exception $e)
    {
        $this->emit('error', array($e));
        $this->close();
    }
    /** @internal */
    public function handleEnd()
    {
        if (!$this->closed) {
            $this->emit('end');
            $this->close();
        }
    }
}
