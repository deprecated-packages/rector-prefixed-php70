<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use Exception;
use PHPStan\BetterReflection\Reflection\Exception\NoObjectProvided;
use PHPStan\BetterReflection\Reflection\Exception\NotAnObject;
use PHPStan\BetterReflection\Reflection\ReflectionProperty as BetterReflectionProperty;
use ReflectionException as CoreReflectionException;
use ReflectionProperty as CoreReflectionProperty;
use Throwable;
class ReflectionProperty extends \ReflectionProperty
{
    /** @var BetterReflectionProperty */
    private $betterReflectionProperty;
    /** @var bool */
    private $accessible = \false;
    public function __construct(\PHPStan\BetterReflection\Reflection\ReflectionProperty $betterReflectionProperty)
    {
        $this->betterReflectionProperty = $betterReflectionProperty;
    }
    public function getBetterReflection() : \PHPStan\BetterReflection\Reflection\ReflectionProperty
    {
        return $this->betterReflectionProperty;
    }
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public static function export($class, $name, $return = null)
    {
        throw new \Exception('Unable to export statically');
    }
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->betterReflectionProperty->__toString();
    }
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->betterReflectionProperty->getName();
    }
    /**
     * {@inheritDoc}
     */
    public function getValue($object = null)
    {
        if (!$this->isAccessible()) {
            throw new \ReflectionException('Property not accessible');
        }
        try {
            return $this->betterReflectionProperty->getValue($object);
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NoObjectProvided $e) {
            return null;
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAnObject $e) {
            return null;
        } catch (\Throwable $e) {
            throw new \ReflectionException($e->getMessage(), 0, $e);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function setValue($object, $value = null)
    {
        if (!$this->isAccessible()) {
            throw new \ReflectionException('Property not accessible');
        }
        try {
            $this->betterReflectionProperty->setValue($object, $value);
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NoObjectProvided $e) {
            return;
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAnObject $e) {
            return;
        } catch (\Throwable $e) {
            throw new \ReflectionException($e->getMessage(), 0, $e);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function hasType()
    {
        return $this->betterReflectionProperty->hasType();
    }
    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionType::fromReturnTypeOrNull($this->betterReflectionProperty->getType());
    }
    /**
     * {@inheritDoc}
     */
    public function isPublic()
    {
        return $this->betterReflectionProperty->isPublic();
    }
    /**
     * {@inheritDoc}
     */
    public function isPrivate()
    {
        return $this->betterReflectionProperty->isPrivate();
    }
    /**
     * {@inheritDoc}
     */
    public function isProtected()
    {
        return $this->betterReflectionProperty->isProtected();
    }
    /**
     * {@inheritDoc}
     */
    public function isStatic()
    {
        return $this->betterReflectionProperty->isStatic();
    }
    /**
     * {@inheritDoc}
     */
    public function isDefault()
    {
        return $this->betterReflectionProperty->isDefault();
    }
    public function isPromoted() : bool
    {
        return $this->betterReflectionProperty->isPromoted();
    }
    /**
     * {@inheritDoc}
     */
    public function getModifiers()
    {
        return $this->betterReflectionProperty->getModifiers();
    }
    /**
     * {@inheritDoc}
     */
    public function getDeclaringClass()
    {
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($this->betterReflectionProperty->getImplementingClass());
    }
    /**
     * {@inheritDoc}
     */
    public function getDocComment()
    {
        return $this->betterReflectionProperty->getDocComment() ?: \false;
    }
    /**
     * {@inheritDoc}
     */
    public function setAccessible($accessible)
    {
        $this->accessible = \true;
    }
    public function isAccessible() : bool
    {
        return $this->accessible || $this->isPublic();
    }
    public function hasDefaultValue() : bool
    {
        return $this->getDefaultValue() !== null;
    }
    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->betterReflectionProperty->getDefaultValue();
    }
}
