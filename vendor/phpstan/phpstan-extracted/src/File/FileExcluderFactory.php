<?php

declare (strict_types=1);
namespace PHPStan\File;

class FileExcluderFactory
{
    /** @var FileExcluderRawFactory */
    private $fileExcluderRawFactory;
    /** @var string[] */
    private $obsoleteExcludesAnalyse;
    /** @var array{analyse?: array<int, string>, analyseAndScan?: array<int, string>}|null */
    private $excludePaths;
    /**
     * @param FileExcluderRawFactory $fileExcluderRawFactory
     * @param string[] $obsoleteExcludesAnalyse
     * @param array{analyse?: array<int, string>, analyseAndScan?: array<int, string>}|null $excludePaths
     */
    public function __construct(\PHPStan\File\FileExcluderRawFactory $fileExcluderRawFactory, array $obsoleteExcludesAnalyse, $excludePaths)
    {
        $this->fileExcluderRawFactory = $fileExcluderRawFactory;
        $this->obsoleteExcludesAnalyse = $obsoleteExcludesAnalyse;
        $this->excludePaths = $excludePaths;
    }
    public function createAnalyseFileExcluder() : \PHPStan\File\FileExcluder
    {
        if ($this->excludePaths === null) {
            return $this->fileExcluderRawFactory->create($this->obsoleteExcludesAnalyse);
        }
        $paths = [];
        if (\array_key_exists('analyse', $this->excludePaths)) {
            $paths = $this->excludePaths['analyse'];
        }
        if (\array_key_exists('analyseAndScan', $this->excludePaths)) {
            $paths = \array_merge($paths, $this->excludePaths['analyseAndScan']);
        }
        return $this->fileExcluderRawFactory->create(\array_values(\array_unique($paths)));
    }
    public function createScanFileExcluder() : \PHPStan\File\FileExcluder
    {
        if ($this->excludePaths === null) {
            return $this->fileExcluderRawFactory->create($this->obsoleteExcludesAnalyse);
        }
        $paths = [];
        if (\array_key_exists('analyseAndScan', $this->excludePaths)) {
            $paths = $this->excludePaths['analyseAndScan'];
        }
        return $this->fileExcluderRawFactory->create(\array_values(\array_unique($paths)));
    }
}
