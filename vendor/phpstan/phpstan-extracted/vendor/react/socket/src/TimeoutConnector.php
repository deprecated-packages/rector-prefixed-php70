<?php

namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Socket;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\Timer;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\Timer\TimeoutException;
final class TimeoutConnector implements \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Socket\ConnectorInterface
{
    private $connector;
    private $timeout;
    private $loop;
    public function __construct(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Socket\ConnectorInterface $connector, $timeout, \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface $loop)
    {
        $this->connector = $connector;
        $this->timeout = $timeout;
        $this->loop = $loop;
    }
    public function connect($uri)
    {
        return \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\Timer\timeout($this->connector->connect($uri), $this->timeout, $this->loop)->then(null, self::handler($uri));
    }
    /**
     * Creates a static rejection handler that reports a proper error message in case of a timeout.
     *
     * This uses a private static helper method to ensure this closure is not
     * bound to this instance and the exception trace does not include a
     * reference to this instance and its connector stack as a result.
     *
     * @param string $uri
     * @return callable
     */
    private static function handler($uri)
    {
        return function (\Exception $e) use($uri) {
            if ($e instanceof \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\React\Promise\Timer\TimeoutException) {
                throw new \RuntimeException('Connection to ' . $uri . ' timed out after ' . $e->getTimeout() . ' seconds', \defined('SOCKET_ETIMEDOUT') ? \SOCKET_ETIMEDOUT : 0);
            }
            throw $e;
        };
    }
}
