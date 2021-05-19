<?php

declare (strict_types=1);
namespace PHPStan\Php;

use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json;
use PHPStan\File\FileReader;
class PhpVersionFactoryFactory
{
    /** @var int|null */
    private $versionId;
    /** @var bool */
    private $readComposerPhpVersion;
    /** @var string[] */
    private $composerAutoloaderProjectPaths;
    /**
     * @param bool $readComposerPhpVersion
     * @param string[] $composerAutoloaderProjectPaths
     * @param int|null $versionId
     */
    public function __construct($versionId, bool $readComposerPhpVersion, array $composerAutoloaderProjectPaths)
    {
        $this->versionId = $versionId;
        $this->readComposerPhpVersion = $readComposerPhpVersion;
        $this->composerAutoloaderProjectPaths = $composerAutoloaderProjectPaths;
    }
    public function create() : \PHPStan\Php\PhpVersionFactory
    {
        $composerPhpVersion = null;
        if ($this->readComposerPhpVersion && \count($this->composerAutoloaderProjectPaths) > 0) {
            $composerJsonPath = \end($this->composerAutoloaderProjectPaths) . '/composer.json';
            if (\is_file($composerJsonPath)) {
                try {
                    $composerJsonContents = \PHPStan\File\FileReader::read($composerJsonPath);
                    $composer = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::decode($composerJsonContents, \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::FORCE_ARRAY);
                    $platformVersion = $composer['config']['platform']['php'] ?? null;
                    if (\is_string($platformVersion)) {
                        $composerPhpVersion = $platformVersion;
                    }
                } catch (\PHPStan\File\CouldNotReadFileException $e) {
                    // pass
                } catch (\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\JsonException $e) {
                    // pass
                }
            }
        }
        return new \PHPStan\Php\PhpVersionFactory($this->versionId, $composerPhpVersion);
    }
}
