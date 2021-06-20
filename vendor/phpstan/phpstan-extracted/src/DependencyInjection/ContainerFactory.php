<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Extensions\PhpExtension;
use Phar;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\Broker\Broker;
use PHPStan\Command\CommandHelper;
use PHPStan\File\FileHelper;
use PHPStan\Php\PhpVersion;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Finder\Finder;
use function sys_get_temp_dir;
/** @api */
class ContainerFactory
{
    /** @var string */
    private $currentWorkingDirectory;
    /** @var FileHelper */
    private $fileHelper;
    /** @var string */
    private $rootDirectory;
    /** @var string */
    private $configDirectory;
    /** @api */
    public function __construct(string $currentWorkingDirectory)
    {
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->fileHelper = new \PHPStan\File\FileHelper($currentWorkingDirectory);
        $rootDir = __DIR__ . '/../..';
        $originalRootDir = $this->fileHelper->normalizePath($rootDir);
        if (\extension_loaded('phar')) {
            $pharPath = \Phar::running(\false);
            if ($pharPath !== '') {
                $rootDir = \dirname($pharPath);
            }
        }
        $this->rootDirectory = $this->fileHelper->normalizePath($rootDir);
        $this->configDirectory = $originalRootDir . '/conf';
    }
    /**
     * @param string $tempDirectory
     * @param string[] $additionalConfigFiles
     * @param string[] $analysedPaths
     * @param string[] $composerAutoloaderProjectPaths
     * @param string[] $analysedPathsFromConfig
     * @param string $usedLevel
     * @param string|null $generateBaselineFile
     * @param string|null $cliAutoloadFile
     * @param string|null $singleReflectionFile
     * @param string|null $singleReflectionInsteadOfFile
     * @return \PHPStan\DependencyInjection\Container
     */
    public function create(string $tempDirectory, array $additionalConfigFiles, array $analysedPaths, array $composerAutoloaderProjectPaths = [], array $analysedPathsFromConfig = [], string $usedLevel = \PHPStan\Command\CommandHelper::DEFAULT_LEVEL, $generateBaselineFile = null, $cliAutoloadFile = null, $singleReflectionFile = null, $singleReflectionInsteadOfFile = null) : \PHPStan\DependencyInjection\Container
    {
        $configurator = new \PHPStan\DependencyInjection\Configurator(new \PHPStan\DependencyInjection\LoaderFactory($this->fileHelper, $this->rootDirectory, $this->currentWorkingDirectory, $generateBaselineFile));
        $configurator->defaultExtensions = ['php' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Extensions\PhpExtension::class, 'extensions' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Extensions\ExtensionsExtension::class];
        $configurator->setDebugMode(\true);
        $configurator->setTempDirectory($tempDirectory);
        $configurator->addParameters(['rootDir' => $this->rootDirectory, 'currentWorkingDirectory' => $this->currentWorkingDirectory, 'cliArgumentsVariablesRegistered' => \ini_get('register_argc_argv') === '1', 'tmpDir' => $tempDirectory, 'additionalConfigFiles' => $additionalConfigFiles, 'analysedPaths' => $analysedPaths, 'composerAutoloaderProjectPaths' => $composerAutoloaderProjectPaths, 'analysedPathsFromConfig' => $analysedPathsFromConfig, 'generateBaselineFile' => $generateBaselineFile, 'usedLevel' => $usedLevel, 'cliAutoloadFile' => $cliAutoloadFile, 'fixerTmpDir' => \sys_get_temp_dir() . '/phpstan-fixer']);
        $configurator->addDynamicParameters(['singleReflectionFile' => $singleReflectionFile, 'singleReflectionInsteadOfFile' => $singleReflectionInsteadOfFile]);
        $configurator->addConfig($this->configDirectory . '/config.neon');
        foreach ($additionalConfigFiles as $additionalConfigFile) {
            $configurator->addConfig($additionalConfigFile);
        }
        $container = $configurator->createContainer();
        \PHPStan\BetterReflection\BetterReflection::$phpVersion = $container->getByType(\PHPStan\Php\PhpVersion::class)->getVersionId();
        \PHPStan\BetterReflection\BetterReflection::populate(
            $container->getService('betterReflectionSourceLocator'),
            // @phpstan-ignore-line
            $container->getService('betterReflectionClassReflector'),
            // @phpstan-ignore-line
            $container->getService('betterReflectionFunctionReflector'),
            // @phpstan-ignore-line
            $container->getService('betterReflectionConstantReflector'),
            // @phpstan-ignore-line
            $container->getService('phpParserDecorator'),
            // @phpstan-ignore-line
            $container->getByType(\PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber::class)
        );
        /** @var Broker $broker */
        $broker = $container->getByType(\PHPStan\Broker\Broker::class);
        \PHPStan\Broker\Broker::registerInstance($broker);
        $container->getService('typeSpecifier');
        return $container->getByType(\PHPStan\DependencyInjection\Container::class);
    }
    /**
     * @return void
     */
    public function clearOldContainers(string $tempDirectory)
    {
        $configurator = new \PHPStan\DependencyInjection\Configurator(new \PHPStan\DependencyInjection\LoaderFactory($this->fileHelper, $this->rootDirectory, $this->currentWorkingDirectory, null));
        $configurator->setDebugMode(\true);
        $configurator->setTempDirectory($tempDirectory);
        $finder = new \RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Finder\Finder();
        $finder->name('Container_*')->in($configurator->getContainerCacheDirectory());
        $twoDaysAgo = \time() - 24 * 60 * 60 * 2;
        foreach ($finder as $containerFile) {
            $path = $containerFile->getRealPath();
            if ($path === \false) {
                continue;
            }
            if ($containerFile->getATime() > $twoDaysAgo) {
                continue;
            }
            if ($containerFile->getCTime() > $twoDaysAgo) {
                continue;
            }
            @\unlink($path);
        }
    }
    public function getCurrentWorkingDirectory() : string
    {
        return $this->currentWorkingDirectory;
    }
    public function getRootDirectory() : string
    {
        return $this->rootDirectory;
    }
    public function getConfigDirectory() : string
    {
        return $this->configDirectory;
    }
}
