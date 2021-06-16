<?php

namespace RectorPrefix20210616\_HumbugBox15516bb2b566\React\Dns\Query;

use RectorPrefix20210616\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface;
use RectorPrefix20210616\_HumbugBox15516bb2b566\React\Promise\Timer;
final class TimeoutExecutor implements \RectorPrefix20210616\_HumbugBox15516bb2b566\React\Dns\Query\ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;
    public function __construct(\RectorPrefix20210616\_HumbugBox15516bb2b566\React\Dns\Query\ExecutorInterface $executor, $timeout, \RectorPrefix20210616\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop)
    {
        $this->executor = $executor;
        $this->loop = $loop;
        $this->timeout = $timeout;
    }
    public function query(\RectorPrefix20210616\_HumbugBox15516bb2b566\React\Dns\Query\Query $query)
    {
        return \RectorPrefix20210616\_HumbugBox15516bb2b566\React\Promise\Timer\timeout($this->executor->query($query), $this->timeout, $this->loop)->then(null, function ($e) use($query) {
            if ($e instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\React\Promise\Timer\TimeoutException) {
                $e = new \RectorPrefix20210616\_HumbugBox15516bb2b566\React\Dns\Query\TimeoutException(\sprintf("DNS query for %s timed out", $query->name), 0, $e);
            }
            throw $e;
        });
    }
}
