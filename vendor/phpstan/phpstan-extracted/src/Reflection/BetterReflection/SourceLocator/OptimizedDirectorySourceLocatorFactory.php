<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceLocator;

use PHPStan\File\FileFinder;
class OptimizedDirectorySourceLocatorFactory
{
    /** @var FileNodesFetcher */
    private $fileNodesFetcher;
    /** @var FileFinder */
    private $fileFinder;
    public function __construct(\PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher $fileNodesFetcher, \PHPStan\File\FileFinder $fileFinder)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
        $this->fileFinder = $fileFinder;
    }
    public function createByDirectory(string $directory) : \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator
    {
        return new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator($this->fileNodesFetcher, $this->fileFinder->findFiles([$directory])->getFiles());
    }
    /**
     * @param string[] $files
     * @return OptimizedDirectorySourceLocator
     */
    public function createByFiles(array $files) : \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator
    {
        return new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator($this->fileNodesFetcher, $files);
    }
}
