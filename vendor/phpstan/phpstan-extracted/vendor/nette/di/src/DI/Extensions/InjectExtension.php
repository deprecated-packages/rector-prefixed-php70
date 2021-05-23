<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection;
/**
 * Calls inject methods and fills @inject properties.
 */
final class InjectExtension extends \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\CompilerExtension
{
    const TAG_INJECT = 'nette.inject';
    public function getConfigSchema() : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure([]);
    }
    public function beforeCompile()
    {
        foreach ($this->getContainerBuilder()->getDefinitions() as $def) {
            if ($def->getTag(self::TAG_INJECT)) {
                $def = $def instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition ? $def->getResultDefinition() : $def;
                if ($def instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition) {
                    $this->updateDefinition($def);
                }
            }
        }
    }
    /**
     * @return void
     */
    private function updateDefinition(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition $def)
    {
        $resolvedType = (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver($this->getContainerBuilder()))->resolveEntityType($def->getFactory());
        $class = \is_subclass_of($resolvedType, $def->getType()) ? $resolvedType : $def->getType();
        $setups = $def->getSetup();
        foreach (self::getInjectProperties($class) as $property => $type) {
            $builder = $this->getContainerBuilder();
            $inject = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement('$' . $property, [\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference::fromType((string) $type)]);
            foreach ($setups as $key => $setup) {
                if ($setup->getEntity() === $inject->getEntity()) {
                    $inject = $setup;
                    $builder = null;
                    unset($setups[$key]);
                }
            }
            self::checkType($class, $property, $type, $builder);
            \array_unshift($setups, $inject);
        }
        foreach (\array_reverse(self::getInjectMethods($class)) as $method) {
            $inject = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement($method);
            foreach ($setups as $key => $setup) {
                if ($setup->getEntity() === $inject->getEntity()) {
                    $inject = $setup;
                    unset($setups[$key]);
                }
            }
            \array_unshift($setups, $inject);
        }
        $def->setSetup($setups);
    }
    /**
     * Generates list of inject methods.
     * @internal
     */
    public static function getInjectMethods(string $class) : array
    {
        $classes = [];
        foreach (\get_class_methods($class) as $name) {
            if (\substr($name, 0, 6) === 'inject') {
                $classes[$name] = (new \ReflectionMethod($class, $name))->getDeclaringClass()->name;
            }
        }
        $methods = \array_keys($classes);
        \uksort($classes, function (string $a, string $b) use($classes, $methods) : int {
            return $classes[$a] === $classes[$b] ? \array_search($a, $methods, \true) <=> \array_search($b, $methods, \true) : (\is_a($classes[$a], $classes[$b], \true) ? 1 : -1);
        });
        return \array_keys($classes);
    }
    /**
     * Generates list of properties with annotation @inject.
     * @internal
     */
    public static function getInjectProperties(string $class) : array
    {
        $res = [];
        foreach (\get_class_vars($class) as $name => $foo) {
            $rp = new \ReflectionProperty($class, $name);
            if (\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::parseAnnotation($rp, 'inject') !== null) {
                if ($type = \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getPropertyType($rp)) {
                } elseif ($type = \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::parseAnnotation($rp, 'var')) {
                    $type = \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::expandClassName($type, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getPropertyDeclaringClass($rp));
                }
                $res[$name] = $type;
            }
        }
        \ksort($res);
        return $res;
    }
    /**
     * Calls all methods starting with with "inject" using autowiring.
     * @param  object  $service
     * @return void
     */
    public static function callInjects(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Container $container, $service)
    {
        if (!\is_object($service)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException(\sprintf('Service must be object, %s given.', \gettype($service)));
        }
        foreach (self::getInjectMethods(\get_class($service)) as $method) {
            $container->callMethod([$service, $method]);
        }
        foreach (self::getInjectProperties(\get_class($service)) as $property => $type) {
            self::checkType($service, $property, $type, $container);
            $service->{$property} = $container->getByType($type);
        }
    }
    /**
     * @param  object|string  $class
     * @param  DI\Container|DI\ContainerBuilder|null  $container
     * @param string|null $type
     * @return void
     */
    private static function checkType($class, string $name, $type, $container)
    {
        $propName = \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::toString(new \ReflectionProperty($class, $name));
        if (!$type) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException("Property {$propName} has no @var annotation.");
        } elseif (!\class_exists($type) && !\interface_exists($type)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException("Class or interface '{$type}' used in @var annotation at {$propName} not found. Check annotation and 'use' statements.");
        } elseif ($container && !$container->getByType($type, \false)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\MissingServiceException("Service of type {$type} used in @var annotation at {$propName} not found. Did you add it to configuration file?");
        }
    }
}
