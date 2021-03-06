<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket;

use RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise;
use InvalidArgumentException;
use RuntimeException;
/**
 * Unix domain socket connector
 *
 * Unix domain sockets use atomic operations, so we can as well emulate
 * async behavior.
 */
final class UnixConnector implements \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\ConnectorInterface
{
    private $loop;
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        $this->loop = $loop;
    }
    public function connect($path)
    {
        if (\strpos($path, '://') === \false) {
            $path = 'unix://' . $path;
        } elseif (\substr($path, 0, 7) !== 'unix://') {
            return \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise\reject(new \InvalidArgumentException('Given URI "' . $path . '" is invalid'));
        }
        $resource = @\stream_socket_client($path, $errno, $errstr, 1.0);
        if (!$resource) {
            return \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise\reject(new \RuntimeException('Unable to connect to unix domain socket "' . $path . '": ' . $errstr, $errno));
        }
        $connection = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\Connection($resource, $this->loop);
        $connection->unix = \true;
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise\resolve($connection);
    }
}
