<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection\Type;

use PHPStan\DependencyInjection\Container;
class LazyDynamicThrowTypeExtensionProvider implements \PHPStan\DependencyInjection\Type\DynamicThrowTypeExtensionProvider
{
    const FUNCTION_TAG = 'phpstan.dynamicFunctionThrowTypeExtension';
    const METHOD_TAG = 'phpstan.dynamicMethodThrowTypeExtension';
    const STATIC_METHOD_TAG = 'phpstan.dynamicStaticMethodThrowTypeExtension';
    /** @var Container */
    private $container;
    public function __construct(\PHPStan\DependencyInjection\Container $container)
    {
        $this->container = $container;
    }
    public function getDynamicFunctionThrowTypeExtensions() : array
    {
        return $this->container->getServicesByTag(self::FUNCTION_TAG);
    }
    public function getDynamicMethodThrowTypeExtensions() : array
    {
        return $this->container->getServicesByTag(self::METHOD_TAG);
    }
    public function getDynamicStaticMethodThrowTypeExtensions() : array
    {
        return $this->container->getServicesByTag(self::STATIC_METHOD_TAG);
    }
}
