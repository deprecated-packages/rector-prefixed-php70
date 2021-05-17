<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflectionExtensionRegistry;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
/**
 * @internal
 */
class DirectClassReflectionExtensionRegistryProvider implements \PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider
{
    /** @var \PHPStan\Reflection\PropertiesClassReflectionExtension[] */
    private $propertiesClassReflectionExtensions;
    /** @var \PHPStan\Reflection\MethodsClassReflectionExtension[] */
    private $methodsClassReflectionExtensions;
    /** @var Broker */
    private $broker;
    /**
     * @param \PHPStan\Reflection\PropertiesClassReflectionExtension[] $propertiesClassReflectionExtensions
     * @param \PHPStan\Reflection\MethodsClassReflectionExtension[] $methodsClassReflectionExtensions
     */
    public function __construct(array $propertiesClassReflectionExtensions, array $methodsClassReflectionExtensions)
    {
        $this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
        $this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
    }
    /**
     * @return void
     */
    public function setBroker(\PHPStan\Broker\Broker $broker)
    {
        $this->broker = $broker;
    }
    /**
     * @return void
     */
    public function addPropertiesClassReflectionExtension(\PHPStan\Reflection\PropertiesClassReflectionExtension $extension)
    {
        $this->propertiesClassReflectionExtensions[] = $extension;
    }
    /**
     * @return void
     */
    public function addMethodsClassReflectionExtension(\PHPStan\Reflection\MethodsClassReflectionExtension $extension)
    {
        $this->methodsClassReflectionExtensions[] = $extension;
    }
    public function getRegistry() : \PHPStan\Reflection\ClassReflectionExtensionRegistry
    {
        return new \PHPStan\Reflection\ClassReflectionExtensionRegistry($this->broker, $this->propertiesClassReflectionExtensions, $this->methodsClassReflectionExtensions);
    }
}
