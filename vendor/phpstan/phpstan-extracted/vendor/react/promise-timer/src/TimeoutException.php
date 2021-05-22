<?php

namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Promise\Timer;

use RuntimeException;
class TimeoutException extends \RuntimeException
{
    private $timeout;
    public function __construct($timeout, $message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->timeout = $timeout;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
}
