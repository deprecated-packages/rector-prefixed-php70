<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket;

use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Resolver\ResolverInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise;
final class HappyEyeBallsConnector implements \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\ConnectorInterface
{
    private $loop;
    private $connector;
    private $resolver;
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\ConnectorInterface $connector, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Resolver\ResolverInterface $resolver)
    {
        $this->loop = $loop;
        $this->connector = $connector;
        $this->resolver = $resolver;
    }
    public function connect($uri)
    {
        if (\strpos($uri, '://') === \false) {
            $parts = \parse_url('tcp://' . $uri);
            unset($parts['scheme']);
        } else {
            $parts = \parse_url($uri);
        }
        if (!$parts || !isset($parts['host'])) {
            return \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise\reject(new \InvalidArgumentException('Given URI "' . $uri . '" is invalid'));
        }
        $host = \trim($parts['host'], '[]');
        // skip DNS lookup / URI manipulation if this URI already contains an IP
        if (\false !== \filter_var($host, \FILTER_VALIDATE_IP)) {
            return $this->connector->connect($uri);
        }
        $builder = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\HappyEyeBallsConnectionBuilder($this->loop, $this->connector, $this->resolver, $uri, $host, $parts);
        return $builder->connect();
    }
}
