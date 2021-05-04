<?php

declare (strict_types=1);
namespace Rector\Core\Autoloading;

use Rector\Core\Configuration\Option;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use RectorPrefix20210504\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210504\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210504\Symplify\SmartFileSystem\FileSystemGuard;
/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
final class AdditionalAutoloader
{
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    public function __construct(\RectorPrefix20210504\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \RectorPrefix20210504\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator)
    {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->parameterProvider = $parameterProvider;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
    }
    /**
     * @return void
     */
    public function autoloadInput(\RectorPrefix20210504\Symfony\Component\Console\Input\InputInterface $input)
    {
        if (!$input->hasOption(\Rector\Core\Configuration\Option::OPTION_AUTOLOAD_FILE)) {
            return;
        }
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(\Rector\Core\Configuration\Option::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }
        $this->fileSystemGuard->ensureFileExists($autoloadFile, 'Extra autoload');
        require_once $autoloadFile;
    }
    /**
     * @return void
     */
    public function autoloadPaths()
    {
        $autoloadPaths = $this->parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::AUTOLOAD_PATHS);
        if ($autoloadPaths === []) {
            return;
        }
        $this->dynamicSourceLocatorDecorator->addPaths($autoloadPaths);
    }
}
