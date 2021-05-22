<?php

declare (strict_types=1);
namespace PHPStan\Parallel;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\EventLoop\TimerInterface;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface;
class Process
{
    /** @var string */
    private $command;
    /** @var \React\ChildProcess\Process */
    public $process;
    /** @var LoopInterface */
    private $loop;
    /** @var float */
    private $timeoutSeconds;
    /** @var WritableStreamInterface */
    private $in;
    /** @var resource */
    private $stdOut;
    /** @var resource */
    private $stdErr;
    /** @var callable(mixed[] $json) : void */
    private $onData;
    /** @var callable(\Throwable $exception) : void */
    private $onError;
    /** @var TimerInterface|null */
    private $timer = null;
    public function __construct(string $command, \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface $loop, float $timeoutSeconds)
    {
        $this->command = $command;
        $this->loop = $loop;
        $this->timeoutSeconds = $timeoutSeconds;
    }
    /**
     * @param callable(mixed[] $json) : void $onData
     * @param callable(\Throwable $exception) : void $onError
     * @param callable(?int $exitCode, string $output) : void $onExit
     * @return void
     */
    public function start(callable $onData, callable $onError, callable $onExit)
    {
        $tmpStdOut = \tmpfile();
        if ($tmpStdOut === \false) {
            throw new \PHPStan\ShouldNotHappenException('Failed creating temp file for stdout.');
        }
        $tmpStdErr = \tmpfile();
        if ($tmpStdErr === \false) {
            throw new \PHPStan\ShouldNotHappenException('Failed creating temp file for stderr.');
        }
        $this->stdOut = $tmpStdOut;
        $this->stdErr = $tmpStdErr;
        $this->process = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\ChildProcess\Process($this->command, null, null, [1 => $this->stdOut, 2 => $this->stdErr]);
        $this->process->start($this->loop);
        $this->onData = $onData;
        $this->onError = $onError;
        $this->process->on('exit', function ($exitCode) use($onExit) {
            $this->cancelTimer();
            $output = '';
            \rewind($this->stdOut);
            $stdOut = \stream_get_contents($this->stdOut);
            if (\is_string($stdOut)) {
                $output .= $stdOut;
            }
            \rewind($this->stdErr);
            $stdErr = \stream_get_contents($this->stdErr);
            if (\is_string($stdErr)) {
                $output .= $stdErr;
            }
            $onExit($exitCode, $output);
            \fclose($this->stdOut);
            \fclose($this->stdErr);
        });
    }
    /**
     * @return void
     */
    private function cancelTimer()
    {
        if ($this->timer === null) {
            return;
        }
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
    /**
     * @param mixed[] $data
     * @return void
     */
    public function request(array $data)
    {
        $this->cancelTimer();
        $this->in->write($data);
        $this->timer = $this->loop->addTimer($this->timeoutSeconds, function () {
            $onError = $this->onError;
            $onError(new \Exception(\sprintf('Child process timed out after %.1f seconds. Try making it longer with parallel.processTimeout setting.', $this->timeoutSeconds)));
        });
    }
    /**
     * @return void
     */
    public function quit()
    {
        $this->cancelTimer();
        if (!$this->process->isRunning()) {
            return;
        }
        foreach ($this->process->pipes as $pipe) {
            $pipe->close();
        }
        $this->in->end();
    }
    /**
     * @return void
     */
    public function bindConnection(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Stream\ReadableStreamInterface $out, \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Stream\WritableStreamInterface $in)
    {
        $out->on('data', function (array $json) {
            if ($json['action'] !== 'result') {
                return;
            }
            $onData = $this->onData;
            $onData($json['result']);
        });
        $this->in = $in;
        $out->on('error', function (\Throwable $error) {
            $onError = $this->onError;
            $onError($error);
        });
        $in->on('error', function (\Throwable $error) {
            $onError = $this->onError;
            $onError($error);
        });
    }
}
