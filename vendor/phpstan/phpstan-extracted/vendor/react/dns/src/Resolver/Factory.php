<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Resolver;

use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Cache\ArrayCache;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Cache\CacheInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Config\HostsFile;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\CachingExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\CoopExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\ExecutorInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\HostsFileExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\RetryExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\SelectiveTransportExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\TcpTransportExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\TimeoutExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\UdpTransportExecutor;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface;
final class Factory
{
    /**
     * @param string        $nameserver
     * @param LoopInterface $loop
     * @return \React\Dns\Resolver\ResolverInterface
     */
    public function create($nameserver, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        $executor = $this->decorateHostsFileExecutor($this->createExecutor($nameserver, $loop));
        return new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * @param string          $nameserver
     * @param LoopInterface   $loop
     * @param ?CacheInterface $cache
     * @return \React\Dns\Resolver\ResolverInterface
     */
    public function createCached($nameserver, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Cache\CacheInterface $cache = null)
    {
        // default to keeping maximum of 256 responses in cache unless explicitly given
        if (!$cache instanceof \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Cache\CacheInterface) {
            $cache = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Cache\ArrayCache(256);
        }
        $executor = $this->createExecutor($nameserver, $loop);
        $executor = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\CachingExecutor($executor, $cache);
        $executor = $this->decorateHostsFileExecutor($executor);
        return new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * Tries to load the hosts file and decorates the given executor on success
     *
     * @param ExecutorInterface $executor
     * @return ExecutorInterface
     * @codeCoverageIgnore
     */
    private function decorateHostsFileExecutor(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\ExecutorInterface $executor)
    {
        try {
            $executor = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\HostsFileExecutor(\RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Config\HostsFile::loadFromPathBlocking(), $executor);
        } catch (\RuntimeException $e) {
            // ignore this file if it can not be loaded
        }
        // Windows does not store localhost in hosts file by default but handles this internally
        // To compensate for this, we explicitly use hard-coded defaults for localhost
        if (\DIRECTORY_SEPARATOR === '\\') {
            $executor = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\HostsFileExecutor(new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Config\HostsFile("127.0.0.1 localhost\n::1 localhost"), $executor);
        }
        return $executor;
    }
    private function createExecutor($nameserver, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        $parts = \parse_url($nameserver);
        if (isset($parts['scheme']) && $parts['scheme'] === 'tcp') {
            $executor = $this->createTcpExecutor($nameserver, $loop);
        } elseif (isset($parts['scheme']) && $parts['scheme'] === 'udp') {
            $executor = $this->createUdpExecutor($nameserver, $loop);
        } else {
            $executor = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\SelectiveTransportExecutor($this->createUdpExecutor($nameserver, $loop), $this->createTcpExecutor($nameserver, $loop));
        }
        return new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\CoopExecutor($executor);
    }
    private function createTcpExecutor($nameserver, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        return new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\TimeoutExecutor(new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\TcpTransportExecutor($nameserver, $loop), 5.0, $loop);
    }
    private function createUdpExecutor($nameserver, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        return new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\RetryExecutor(new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\TimeoutExecutor(new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Dns\Query\UdpTransportExecutor($nameserver, $loop), 5.0, $loop));
    }
}
