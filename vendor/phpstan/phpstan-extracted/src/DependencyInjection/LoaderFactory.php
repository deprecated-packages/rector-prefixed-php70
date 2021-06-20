<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\Loader;
use PHPStan\File\FileHelper;
class LoaderFactory
{
    /** @var FileHelper */
    private $fileHelper;
    /** @var string */
    private $rootDir;
    /** @var string */
    private $currentWorkingDirectory;
    /** @var string|null */
    private $generateBaselineFile;
    /**
     * @param string|null $generateBaselineFile
     */
    public function __construct(\PHPStan\File\FileHelper $fileHelper, string $rootDir, string $currentWorkingDirectory, $generateBaselineFile)
    {
        $this->fileHelper = $fileHelper;
        $this->rootDir = $rootDir;
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->generateBaselineFile = $generateBaselineFile;
    }
    public function createLoader() : \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\Loader
    {
        $loader = new \PHPStan\DependencyInjection\NeonLoader($this->fileHelper, $this->generateBaselineFile);
        $loader->addAdapter('dist', \PHPStan\DependencyInjection\NeonAdapter::class);
        $loader->addAdapter('neon', \PHPStan\DependencyInjection\NeonAdapter::class);
        $loader->setParameters(['rootDir' => $this->rootDir, 'currentWorkingDirectory' => $this->currentWorkingDirectory]);
        return $loader;
    }
}
