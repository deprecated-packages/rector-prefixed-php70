<?php

namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Http\Io;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Evenement\EventEmitter;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\Util;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface;
/**
 * [Internal] Encodes given payload stream with "Transfer-Encoding: chunked" and emits encoded data
 *
 * This is used internally to encode outgoing requests with this encoding.
 *
 * @internal
 */
class ChunkedEncoder extends \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Evenement\EventEmitter implements \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface
{
    private $input;
    private $closed = \false;
    public function __construct(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface $input)
    {
        $this->input = $input;
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
    public function pipe(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface $dest, array $options = array())
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\React\Stream\Util::pipe($this, $dest, $options);
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
    /** @internal */
    public function handleData($data)
    {
        if ($data !== '') {
            $this->emit('data', array(\dechex(\strlen($data)) . "\r\n" . $data . "\r\n"));
        }
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
        $this->emit('data', array("0\r\n\r\n"));
        if (!$this->closed) {
            $this->emit('end');
            $this->close();
        }
    }
}
