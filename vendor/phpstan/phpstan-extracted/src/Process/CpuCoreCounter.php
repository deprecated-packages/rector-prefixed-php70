<?php

declare (strict_types=1);
namespace PHPStan\Process;

class CpuCoreCounter
{
    /** @var int|null */
    private $count = null;
    public function getNumberOfCpuCores() : int
    {
        if ($this->count !== null) {
            return $this->count;
        }
        if (!\function_exists('proc_open')) {
            return $this->count = 1;
        }
        // from brianium/paratest
        if (@\is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = @\file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== \false) {
                \preg_match_all('/^processor/m', $cpuinfo, $matches);
                return $this->count = \count($matches[0]);
            }
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            // Windows
            $process = @\popen('wmic cpu get NumberOfLogicalProcessors', 'rb');
            if (\is_resource($process)) {
                \fgets($process);
                $cores = (int) \fgets($process);
                \pclose($process);
                return $this->count = $cores;
            }
        }
        $process = @\popen('sysctl -n hw.ncpu', 'rb');
        if (\is_resource($process)) {
            // *nix (Linux, BSD and Mac)
            $cores = (int) \fgets($process);
            \pclose($process);
            return $this->count = $cores;
        }
        return $this->count = 2;
    }
}
