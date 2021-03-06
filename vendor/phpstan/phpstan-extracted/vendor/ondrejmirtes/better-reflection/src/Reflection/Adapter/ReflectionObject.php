<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use PHPStan\BetterReflection\Reflection\Exception\NotAnObject;
use PHPStan\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionMethod as BetterReflectionMethod;
use PHPStan\BetterReflection\Reflection\ReflectionObject as BetterReflectionObject;
use PHPStan\BetterReflection\Reflection\ReflectionProperty as BetterReflectionProperty;
use PHPStan\BetterReflection\Util\FileHelper;
use ReflectionException as CoreReflectionException;
use ReflectionObject as CoreReflectionObject;
use function array_combine;
use function array_map;
use function array_values;
use function assert;
use function func_num_args;
use function is_array;
use function sprintf;
use function strtolower;
class ReflectionObject extends \ReflectionObject
{
    /** @var BetterReflectionObject */
    private $betterReflectionObject;
    public function __construct(\PHPStan\BetterReflection\Reflection\ReflectionObject $betterReflectionObject)
    {
        $this->betterReflectionObject = $betterReflectionObject;
    }
    /**
     * {@inheritDoc}
     *
     * @throws CoreReflectionException
     */
    public static function export($argument, $return = null)
    {
        $output = \PHPStan\BetterReflection\Reflection\ReflectionObject::createFromInstance($argument)->__toString();
        if ($return) {
            return $output;
        }
        echo $output;
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->betterReflectionObject->__toString();
    }
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->betterReflectionObject->getName();
    }
    /**
     * {@inheritDoc}
     */
    public function isInternal()
    {
        return $this->betterReflectionObject->isInternal();
    }
    /**
     * {@inheritDoc}
     */
    public function isUserDefined()
    {
        return $this->betterReflectionObject->isUserDefined();
    }
    /**
     * {@inheritDoc}
     */
    public function isInstantiable()
    {
        return $this->betterReflectionObject->isInstantiable();
    }
    /**
     * {@inheritDoc}
     */
    public function isCloneable()
    {
        return $this->betterReflectionObject->isCloneable();
    }
    /**
     * {@inheritDoc}
     */
    public function getFileName()
    {
        $fileName = $this->betterReflectionObject->getFileName();
        return $fileName !== null ? \PHPStan\BetterReflection\Util\FileHelper::normalizeSystemPath($fileName) : \false;
    }
    /**
     * {@inheritDoc}
     */
    public function getStartLine()
    {
        return $this->betterReflectionObject->getStartLine();
    }
    /**
     * {@inheritDoc}
     */
    public function getEndLine()
    {
        return $this->betterReflectionObject->getEndLine();
    }
    /**
     * {@inheritDoc}
     */
    public function getDocComment()
    {
        return $this->betterReflectionObject->getDocComment() ?: \false;
    }
    /**
     * {@inheritDoc}
     */
    public function getConstructor()
    {
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod($this->betterReflectionObject->getConstructor());
    }
    /**
     * {@inheritDoc}
     */
    public function hasMethod($name)
    {
        return $this->betterReflectionObject->hasMethod($this->getMethodRealName($name));
    }
    /**
     * {@inheritDoc}
     */
    public function getMethod($name)
    {
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod($this->betterReflectionObject->getMethod($this->getMethodRealName($name)));
    }
    private function getMethodRealName(string $name) : string
    {
        $realMethodNames = \array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionMethod $method) : string {
            return $method->getName();
        }, $this->betterReflectionObject->getMethods());
        $methodNames = \array_combine(\array_map(static function (string $methodName) : string {
            return \strtolower($methodName);
        }, $realMethodNames), $realMethodNames);
        return $methodNames[\strtolower($name)] ?? $name;
    }
    /**
     * {@inheritDoc}
     */
    public function getMethods($filter = null)
    {
        $methods = $this->betterReflectionObject->getMethods();
        $wrappedMethods = [];
        foreach ($methods as $key => $method) {
            $wrappedMethods[$key] = new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod($method);
        }
        return $wrappedMethods;
    }
    /**
     * {@inheritDoc}
     */
    public function hasProperty($name)
    {
        return $this->betterReflectionObject->hasProperty($name);
    }
    /**
     * {@inheritDoc}
     */
    public function getProperty($name)
    {
        $property = $this->betterReflectionObject->getProperty($name);
        if ($property === null) {
            throw new \ReflectionException(\sprintf('Property "%s" does not exist', $name));
        }
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty($property);
    }
    /**
     * {@inheritDoc}
     */
    public function getProperties($filter = null)
    {
        return \array_values(\array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionProperty $property) : ReflectionProperty {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty($property);
        }, $this->betterReflectionObject->getProperties()));
    }
    /**
     * {@inheritDoc}
     */
    public function hasConstant($name)
    {
        return $this->betterReflectionObject->hasConstant($name);
    }
    /**
     * {@inheritDoc}
     */
    public function getConstants(int $filter = null)
    {
        return $this->betterReflectionObject->getConstants($filter);
    }
    /**
     * {@inheritDoc}
     */
    public function getConstant($name)
    {
        return $this->betterReflectionObject->getConstant($name);
    }
    /**
     * {@inheritDoc}
     */
    public function getInterfaces()
    {
        $interfaces = $this->betterReflectionObject->getInterfaces();
        $wrappedInterfaces = [];
        foreach ($interfaces as $key => $interface) {
            $wrappedInterfaces[$key] = new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($interface);
        }
        return $wrappedInterfaces;
    }
    /**
     * {@inheritDoc}
     */
    public function getInterfaceNames()
    {
        return $this->betterReflectionObject->getInterfaceNames();
    }
    /**
     * {@inheritDoc}
     */
    public function isInterface()
    {
        return $this->betterReflectionObject->isInterface();
    }
    /**
     * {@inheritDoc}
     */
    public function getTraits()
    {
        $traits = $this->betterReflectionObject->getTraits();
        /** @var array<trait-string> $traitNames */
        $traitNames = \array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionClass $trait) : string {
            return $trait->getName();
        }, $traits);
        $traitsByName = \array_combine($traitNames, \array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionClass $trait) : ReflectionClass {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($trait);
        }, $traits));
        \assert(\is_array($traitsByName), \sprintf('Could not create an array<trait-string, ReflectionClass> for class "%s"', $this->betterReflectionObject->getName()));
        return $traitsByName;
    }
    /**
     * {@inheritDoc}
     */
    public function getTraitNames()
    {
        return $this->betterReflectionObject->getTraitNames();
    }
    /**
     * {@inheritDoc}
     */
    public function getTraitAliases()
    {
        return $this->betterReflectionObject->getTraitAliases();
    }
    /**
     * {@inheritDoc}
     */
    public function isTrait()
    {
        return $this->betterReflectionObject->isTrait();
    }
    /**
     * {@inheritDoc}
     */
    public function isAbstract()
    {
        return $this->betterReflectionObject->isAbstract();
    }
    /**
     * {@inheritDoc}
     */
    public function isFinal()
    {
        return $this->betterReflectionObject->isFinal();
    }
    /**
     * {@inheritDoc}
     */
    public function getModifiers()
    {
        return $this->betterReflectionObject->getModifiers();
    }
    /**
     * {@inheritDoc}
     */
    public function isInstance($object)
    {
        try {
            return $this->betterReflectionObject->isInstance($object);
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAnObject $e) {
            return null;
        }
    }
    /**
     * {@inheritDoc}
     */
    public function newInstance($arg = null, ...$args)
    {
        throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
    }
    /**
     * {@inheritDoc}
     */
    public function newInstanceWithoutConstructor()
    {
        throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
    }
    /**
     * {@inheritDoc}
     * @param mixed[]|null $args
     */
    public function newInstanceArgs($args = null)
    {
        throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
    }
    /**
     * {@inheritDoc}
     */
    public function getParentClass()
    {
        $parentClass = $this->betterReflectionObject->getParentClass();
        if ($parentClass === null) {
            return \false;
        }
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($parentClass);
    }
    /**
     * {@inheritDoc}
     */
    public function isSubclassOf($class)
    {
        $realParentClassNames = $this->betterReflectionObject->getParentClassNames();
        $parentClassNames = \array_combine(\array_map(static function (string $parentClassName) : string {
            return \strtolower($parentClassName);
        }, $realParentClassNames), $realParentClassNames);
        $realParentClassName = $parentClassNames[\strtolower($class)] ?? $class;
        return $this->betterReflectionObject->isSubclassOf($realParentClassName);
    }
    /**
     * {@inheritDoc}
     */
    public function getStaticProperties()
    {
        return $this->betterReflectionObject->getStaticProperties();
    }
    /**
     * {@inheritDoc}
     */
    public function getStaticPropertyValue($name, $default = null)
    {
        $betterReflectionProperty = $this->betterReflectionObject->getProperty($name);
        if ($betterReflectionProperty === null) {
            if (\func_num_args() === 2) {
                return $default;
            }
            throw new \ReflectionException(\sprintf('Property "%s" does not exist', $name));
        }
        $property = new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty($betterReflectionProperty);
        if (!$property->isAccessible()) {
            throw new \ReflectionException(\sprintf('Property "%s" is not accessible', $name));
        }
        if (!$property->isStatic()) {
            throw new \ReflectionException(\sprintf('Property "%s" is not static', $name));
        }
        return $property->getValue();
    }
    /**
     * {@inheritDoc}
     */
    public function setStaticPropertyValue($name, $value)
    {
        $betterReflectionProperty = $this->betterReflectionObject->getProperty($name);
        if ($betterReflectionProperty === null) {
            throw new \ReflectionException(\sprintf('Property "%s" does not exist', $name));
        }
        $property = new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty($betterReflectionProperty);
        if (!$property->isAccessible()) {
            throw new \ReflectionException(\sprintf('Property "%s" is not accessible', $name));
        }
        if (!$property->isStatic()) {
            throw new \ReflectionException(\sprintf('Property "%s" is not static', $name));
        }
        $property->setValue($value);
    }
    /**
     * {@inheritDoc}
     */
    public function getDefaultProperties()
    {
        return $this->betterReflectionObject->getDefaultProperties();
    }
    /**
     * {@inheritDoc}
     */
    public function isIterateable()
    {
        return $this->betterReflectionObject->isIterateable();
    }
    /**
     * {@inheritDoc}
     */
    public function implementsInterface($interface)
    {
        $realInterfaceNames = $this->betterReflectionObject->getInterfaceNames();
        $interfaceNames = \array_combine(\array_map(static function (string $interfaceName) : string {
            return \strtolower($interfaceName);
        }, $realInterfaceNames), $realInterfaceNames);
        $realInterfaceName = $interfaceNames[\strtolower($interface)] ?? $interface;
        return $this->betterReflectionObject->implementsInterface($realInterfaceName);
    }
    /**
     * {@inheritDoc}
     */
    public function getExtension()
    {
        throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
    }
    /**
     * {@inheritDoc}
     */
    public function getExtensionName()
    {
        return $this->betterReflectionObject->getExtensionName() ?? \false;
    }
    /**
     * {@inheritDoc}
     */
    public function inNamespace()
    {
        return $this->betterReflectionObject->inNamespace();
    }
    /**
     * {@inheritDoc}
     */
    public function getNamespaceName()
    {
        return $this->betterReflectionObject->getNamespaceName();
    }
    /**
     * {@inheritDoc}
     */
    public function getShortName()
    {
        return $this->betterReflectionObject->getShortName();
    }
}
