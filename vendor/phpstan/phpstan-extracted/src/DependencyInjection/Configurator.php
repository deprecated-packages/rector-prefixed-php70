<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Loader;
use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerLoader;
class Configurator extends \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\Configurator
{
    /** @var LoaderFactory */
    private $loaderFactory;
    public function __construct(\PHPStan\DependencyInjection\LoaderFactory $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
        parent::__construct();
    }
    protected function createLoader() : \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Loader
    {
        return $this->loaderFactory->createLoader();
    }
    /**
     * @return mixed[]
     */
    protected function getDefaultParameters() : array
    {
        return [];
    }
    public function getContainerCacheDirectory() : string
    {
        return $this->getCacheDirectory() . '/nette.configurator';
    }
    public function loadContainer() : string
    {
        $loader = new \RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerLoader($this->getContainerCacheDirectory(), $this->parameters['debugMode']);
        return $loader->load([$this, 'generateContainer'], [$this->parameters, \array_keys($this->dynamicParameters), $this->configs, \PHP_VERSION_ID - \PHP_RELEASE_VERSION, \PHPStan\DependencyInjection\NeonAdapter::CACHE_KEY]);
    }
}
