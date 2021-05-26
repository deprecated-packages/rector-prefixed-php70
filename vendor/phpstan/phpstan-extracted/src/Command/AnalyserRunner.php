<?php

declare (strict_types=1);
namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Parallel\ParallelAnalyser;
use PHPStan\Parallel\Scheduler;
use PHPStan\Process\CpuCoreCounter;
use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputInterface;
class AnalyserRunner
{
    /** @var Scheduler */
    private $scheduler;
    /** @var Analyser */
    private $analyser;
    /** @var ParallelAnalyser */
    private $parallelAnalyser;
    /** @var CpuCoreCounter */
    private $cpuCoreCounter;
    public function __construct(\PHPStan\Parallel\Scheduler $scheduler, \PHPStan\Analyser\Analyser $analyser, \PHPStan\Parallel\ParallelAnalyser $parallelAnalyser, \PHPStan\Process\CpuCoreCounter $cpuCoreCounter)
    {
        $this->scheduler = $scheduler;
        $this->analyser = $analyser;
        $this->parallelAnalyser = $parallelAnalyser;
        $this->cpuCoreCounter = $cpuCoreCounter;
    }
    /**
     * @param string[] $files
     * @param string[] $allAnalysedFiles
     * @param (\Closure(string $file): void)|null $preFileCallback
     * @param (\Closure(int): void)|null $postFileCallback
     * @param bool $debug
     * @param bool $allowParallel
     * @param string|null $projectConfigFile
     * @param string|null $tmpFile
     * @param string|null $insteadOfFile
     * @param InputInterface $input
     * @return AnalyserResult
     */
    public function runAnalyser(array $files, array $allAnalysedFiles, $preFileCallback, $postFileCallback, bool $debug, bool $allowParallel, $projectConfigFile, $tmpFile, $insteadOfFile, \RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputInterface $input) : \PHPStan\Analyser\AnalyserResult
    {
        $filesCount = \count($files);
        if ($filesCount === 0) {
            return new \PHPStan\Analyser\AnalyserResult([], [], [], [], \false);
        }
        $schedule = $this->scheduler->scheduleWork($this->cpuCoreCounter->getNumberOfCpuCores(), $files);
        $mainScript = null;
        if (isset($_SERVER['argv'][0]) && \file_exists($_SERVER['argv'][0])) {
            $mainScript = $_SERVER['argv'][0];
        }
        if (!$debug && $allowParallel && $mainScript !== null && $schedule->getNumberOfProcesses() > 1) {
            return $this->parallelAnalyser->analyse($schedule, $mainScript, $postFileCallback, $projectConfigFile, $tmpFile, $insteadOfFile, $input);
        }
        return $this->analyser->analyse($this->switchTmpFile($files, $insteadOfFile, $tmpFile), $preFileCallback, $postFileCallback, $debug, $this->switchTmpFile($allAnalysedFiles, $insteadOfFile, $tmpFile));
    }
    /**
     * @param string[] $analysedFiles
     * @param string|null $insteadOfFile
     * @param string|null $tmpFile
     * @return string[]
     */
    private function switchTmpFile(array $analysedFiles, $insteadOfFile, $tmpFile) : array
    {
        $analysedFiles = \array_values(\array_filter($analysedFiles, static function (string $file) use($insteadOfFile) : bool {
            if ($insteadOfFile === null) {
                return \true;
            }
            return $file !== $insteadOfFile;
        }));
        if ($tmpFile !== null) {
            $analysedFiles[] = $tmpFile;
        }
        return $analysedFiles;
    }
}
