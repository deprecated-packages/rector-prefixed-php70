<?php

declare (strict_types=1);
namespace PHPStan\Process\Runnable;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred;
use SplObjectStorage;
class RunnableQueue
{
    /** @var RunnableQueueLogger */
    private $logger;
    /** @var int */
    private $maxSize;
    /** @var array<array{Runnable, int, Deferred}> */
    private $queue = [];
    /** @var SplObjectStorage<Runnable, array{int, Deferred}> */
    private $running;
    public function __construct(\PHPStan\Process\Runnable\RunnableQueueLogger $logger, int $maxSize)
    {
        $this->logger = $logger;
        $this->maxSize = $maxSize;
        /** @var SplObjectStorage<Runnable, array{int, Deferred}> $running */
        $running = new \SplObjectStorage();
        $this->running = $running;
    }
    public function getQueueSize() : int
    {
        $allSize = 0;
        foreach ($this->queue as list($runnable, $size, $deferred)) {
            $allSize += $size;
        }
        return $allSize;
    }
    public function getRunningSize() : int
    {
        $allSize = 0;
        foreach ($this->running as $running) {
            // phpcs:ignore
            list($size) = $this->running->getInfo();
            $allSize += $size;
        }
        return $allSize;
    }
    public function queue(\PHPStan\Process\Runnable\Runnable $runnable, int $size) : \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface
    {
        if ($size > $this->maxSize) {
            throw new \PHPStan\ShouldNotHappenException('Runnable size exceeds queue maxSize.');
        }
        $deferred = new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred(static function () use($runnable) {
            $runnable->cancel();
        });
        $this->queue[] = [$runnable, $size, $deferred];
        $this->drainQueue();
        /** @var CancellablePromiseInterface */
        return $deferred->promise();
    }
    /**
     * @return void
     */
    private function drainQueue()
    {
        if (\count($this->queue) === 0) {
            $this->logger->log('Queue empty');
            return;
        }
        $currentQueueSize = $this->getRunningSize();
        if ($currentQueueSize > $this->maxSize) {
            throw new \PHPStan\ShouldNotHappenException('Running overflow');
        }
        if ($currentQueueSize === $this->maxSize) {
            $this->logger->log('Queue is full');
            return;
        }
        $this->logger->log('Queue not full - looking at first item in the queue');
        list($runnable, $runnableSize, $deferred) = $this->queue[0];
        $newSize = $currentQueueSize + $runnableSize;
        if ($newSize > $this->maxSize) {
            $this->logger->log(\sprintf('Canot remote first item from the queue - it has size %d, current queue size is %d, new size would be %d', $runnableSize, $currentQueueSize, $newSize));
            return;
        }
        $this->logger->log(\sprintf('Removing top item from queue - new size is %d', $newSize));
        /** @var array{Runnable, int, Deferred} $popped */
        $popped = \array_shift($this->queue);
        if ($popped[0] !== $runnable || $popped[1] !== $runnableSize || $popped[2] !== $deferred) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $this->running->attach($runnable, [$runnableSize, $deferred]);
        $this->logger->log(\sprintf('Running process %s', $runnable->getName()));
        $runnable->run()->then(function ($value) use($runnable, $deferred) {
            $this->logger->log(\sprintf('Process %s finished successfully', $runnable->getName()));
            $deferred->resolve($value);
            $this->running->detach($runnable);
            $this->drainQueue();
        }, function (\Throwable $e) use($runnable, $deferred) {
            $this->logger->log(\sprintf('Process %s finished unsuccessfully: %s', $runnable->getName(), $e->getMessage()));
            $deferred->reject($e);
            $this->running->detach($runnable);
            $this->drainQueue();
        });
    }
    /**
     * @return void
     */
    public function cancelAll()
    {
        foreach ($this->queue as list($runnable, $size, $deferred)) {
            $deferred->promise()->cancel();
            // @phpstan-ignore-line
        }
        $runningDeferreds = [];
        foreach ($this->running as $running) {
            // phpcs:ignore
            list(, $deferred) = $this->running->getInfo();
            $runningDeferreds[] = $deferred;
        }
        foreach ($runningDeferreds as $deferred) {
            $deferred->promise()->cancel();
            // @phpstan-ignore-line
        }
    }
}
