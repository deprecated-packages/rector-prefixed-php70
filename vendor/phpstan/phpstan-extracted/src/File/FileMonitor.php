<?php

declare (strict_types=1);
namespace PHPStan\File;

use function array_key_exists;
class FileMonitor
{
    /** @var FileFinder */
    private $fileFinder;
    /** @var array<string, string>|null */
    private $fileHashes;
    /** @var array<string>|null */
    private $paths;
    public function __construct(\PHPStan\File\FileFinder $fileFinder)
    {
        $this->fileFinder = $fileFinder;
    }
    /**
     * @param array<string> $paths
     * @return void
     */
    public function initialize(array $paths)
    {
        $finderResult = $this->fileFinder->findFiles($paths);
        $fileHashes = [];
        foreach ($finderResult->getFiles() as $filePath) {
            $fileHashes[$filePath] = $this->getFileHash($filePath);
        }
        $this->fileHashes = $fileHashes;
        $this->paths = $paths;
    }
    public function getChanges() : \PHPStan\File\FileMonitorResult
    {
        if ($this->fileHashes === null || $this->paths === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $finderResult = $this->fileFinder->findFiles($this->paths);
        $oldFileHashes = $this->fileHashes;
        $fileHashes = [];
        $newFiles = [];
        $changedFiles = [];
        $deletedFiles = [];
        foreach ($finderResult->getFiles() as $filePath) {
            if (!\array_key_exists($filePath, $oldFileHashes)) {
                $newFiles[] = $filePath;
                $fileHashes[$filePath] = $this->getFileHash($filePath);
                continue;
            }
            $oldHash = $oldFileHashes[$filePath];
            unset($oldFileHashes[$filePath]);
            $newHash = $this->getFileHash($filePath);
            $fileHashes[$filePath] = $newHash;
            if ($oldHash === $newHash) {
                continue;
            }
            $changedFiles[] = $filePath;
        }
        $this->fileHashes = $fileHashes;
        foreach (\array_keys($oldFileHashes) as $file) {
            $deletedFiles[] = $file;
        }
        return new \PHPStan\File\FileMonitorResult($newFiles, $changedFiles, $deletedFiles, \count($fileHashes));
    }
    private function getFileHash(string $filePath) : string
    {
        return \sha1(\PHPStan\File\FileReader::read($filePath));
    }
}
