<?php

declare (strict_types=1);
namespace PHPStan\Parallel;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Socket\TcpServer;
use function array_key_exists;
class ProcessPool
{
    /** @var TcpServer */
    private $server;
    /** @var array<string, Process> */
    private $processes = [];
    public function __construct(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\React\Socket\TcpServer $server)
    {
        $this->server = $server;
    }
    public function getProcess(string $identifier) : \PHPStan\Parallel\Process
    {
        if (!\array_key_exists($identifier, $this->processes)) {
            throw new \PHPStan\ShouldNotHappenException(\sprintf('Process %s not found.', $identifier));
        }
        return $this->processes[$identifier];
    }
    /**
     * @return void
     */
    public function attachProcess(string $identifier, \PHPStan\Parallel\Process $process)
    {
        $this->processes[$identifier] = $process;
    }
    /**
     * @return void
     */
    public function tryQuitProcess(string $identifier)
    {
        if (!\array_key_exists($identifier, $this->processes)) {
            return;
        }
        $this->quitProcess($identifier);
    }
    /**
     * @return void
     */
    private function quitProcess(string $identifier)
    {
        $process = $this->getProcess($identifier);
        $process->quit();
        unset($this->processes[$identifier]);
        if (\count($this->processes) !== 0) {
            return;
        }
        $this->server->close();
    }
    /**
     * @return void
     */
    public function quitAll()
    {
        foreach (\array_keys($this->processes) as $identifier) {
            $this->quitProcess($identifier);
        }
    }
}
