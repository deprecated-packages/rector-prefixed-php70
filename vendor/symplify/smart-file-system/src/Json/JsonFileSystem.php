<?php

declare (strict_types=1);
namespace Symplify\SmartFileSystem\Json;

use RectorPrefix20210522\Nette\Utils\Arrays;
use RectorPrefix20210522\Nette\Utils\Json;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @see \Symplify\SmartFileSystem\Tests\Json\JsonFileSystem\JsonFileSystemTest
 */
final class JsonFileSystem
{
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->smartFileSystem = $smartFileSystem;
    }
    /**
     * @return mixed[]
     */
    public function loadFilePathToJson(string $filePath) : array
    {
        $this->fileSystemGuard->ensureFileExists($filePath, __METHOD__);
        $fileContent = $this->smartFileSystem->readFile($filePath);
        return \RectorPrefix20210522\Nette\Utils\Json::decode($fileContent, \RectorPrefix20210522\Nette\Utils\Json::FORCE_ARRAY);
    }
    /**
     * @param array<string, mixed> $jsonArray
     * @return void
     */
    public function writeJsonToFilePath(array $jsonArray, string $filePath)
    {
        $jsonContent = \RectorPrefix20210522\Nette\Utils\Json::encode($jsonArray, \RectorPrefix20210522\Nette\Utils\Json::PRETTY) . \PHP_EOL;
        $this->smartFileSystem->dumpFile($filePath, $jsonContent);
    }
    /**
     * @param array<string, mixed> $newJsonArray
     * @return void
     */
    public function mergeArrayToJsonFile(string $filePath, array $newJsonArray)
    {
        $jsonArray = $this->loadFilePathToJson($filePath);
        $newComposerJsonArray = \RectorPrefix20210522\Nette\Utils\Arrays::mergeTree($jsonArray, $newJsonArray);
        $this->writeJsonToFilePath($newComposerJsonArray, $filePath);
    }
}
