<?php

declare (strict_types=1);
namespace PHPStan\Process;

use PHPStan\Process\Runnable\Runnable;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\ChildProcess\Process;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Promise\ExtendedPromiseInterface;
class ProcessPromise implements \PHPStan\Process\Runnable\Runnable
{
    /** @var LoopInterface */
    private $loop;
    /** @var string */
    private $name;
    /** @var string */
    private $command;
    /** @var Deferred */
    private $deferred;
    /** @var Process|null */
    private $process = null;
    /** @var bool */
    private $canceled = \false;
    public function __construct(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface $loop, string $name, string $command)
    {
        $this->loop = $loop;
        $this->name = $name;
        $this->command = $command;
        $this->deferred = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Promise\Deferred();
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return ExtendedPromiseInterface&CancellablePromiseInterface
     */
    public function run() : \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface
    {
        $tmpStdOutResource = \tmpfile();
        if ($tmpStdOutResource === \false) {
            throw new \PHPStan\ShouldNotHappenException('Failed creating temp file for stdout.');
        }
        $tmpStdErrResource = \tmpfile();
        if ($tmpStdErrResource === \false) {
            throw new \PHPStan\ShouldNotHappenException('Failed creating temp file for stderr.');
        }
        $this->process = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\React\ChildProcess\Process($this->command, null, null, [1 => $tmpStdOutResource, 2 => $tmpStdErrResource]);
        $this->process->start($this->loop);
        $this->process->on('exit', function ($exitCode) use($tmpStdOutResource, $tmpStdErrResource) {
            if ($this->canceled) {
                \fclose($tmpStdOutResource);
                \fclose($tmpStdErrResource);
                return;
            }
            \rewind($tmpStdOutResource);
            $stdOut = \stream_get_contents($tmpStdOutResource);
            \fclose($tmpStdOutResource);
            \rewind($tmpStdErrResource);
            $stdErr = \stream_get_contents($tmpStdErrResource);
            \fclose($tmpStdErrResource);
            if ($exitCode === null) {
                $this->deferred->reject(new \PHPStan\Process\ProcessCrashedException($stdOut . $stdErr));
                return;
            }
            if ($exitCode === 0) {
                $this->deferred->resolve($stdOut);
                return;
            }
            $this->deferred->reject(new \PHPStan\Process\ProcessCrashedException($stdOut . $stdErr));
        });
        /** @var ExtendedPromiseInterface&CancellablePromiseInterface */
        return $this->deferred->promise();
    }
    /**
     * @return void
     */
    public function cancel()
    {
        if ($this->process === null) {
            throw new \PHPStan\ShouldNotHappenException('Cancelling process before running');
        }
        $this->canceled = \true;
        $this->process->terminate();
        $this->deferred->reject(new \PHPStan\Process\ProcessCanceledException());
    }
}
