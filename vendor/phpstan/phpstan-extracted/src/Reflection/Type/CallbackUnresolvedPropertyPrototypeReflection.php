<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Dummy\ChangedTypePropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ResolvedPropertyReflection;
use PHPStan\Type\Type;
class CallbackUnresolvedPropertyPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
{
    /** @var PropertyReflection */
    private $propertyReflection;
    /** @var ClassReflection */
    private $resolvedDeclaringClass;
    /** @var bool */
    private $resolveTemplateTypeMapToBounds;
    /** @var callable(Type): Type */
    private $transformStaticTypeCallback;
    /** @var PropertyReflection|null */
    private $transformedProperty = null;
    /** @var self|null */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    /**
     * @param PropertyReflection $propertyReflection
     * @param ClassReflection $resolvedDeclaringClass
     * @param bool $resolveTemplateTypeMapToBounds
     * @param callable(Type): Type $transformStaticTypeCallback
     */
    public function __construct(\PHPStan\Reflection\PropertyReflection $propertyReflection, \PHPStan\Reflection\ClassReflection $resolvedDeclaringClass, bool $resolveTemplateTypeMapToBounds, callable $transformStaticTypeCallback)
    {
        $this->propertyReflection = $propertyReflection;
        $this->resolvedDeclaringClass = $resolvedDeclaringClass;
        $this->resolveTemplateTypeMapToBounds = $resolveTemplateTypeMapToBounds;
        $this->transformStaticTypeCallback = $transformStaticTypeCallback;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->propertyReflection, $this->resolvedDeclaringClass, \false, $this->transformStaticTypeCallback);
    }
    public function getNakedProperty() : \PHPStan\Reflection\PropertyReflection
    {
        return $this->propertyReflection;
    }
    public function getTransformedProperty() : \PHPStan\Reflection\PropertyReflection
    {
        if ($this->transformedProperty !== null) {
            return $this->transformedProperty;
        }
        $templateTypeMap = $this->resolvedDeclaringClass->getActiveTemplateTypeMap();
        return $this->transformedProperty = new \PHPStan\Reflection\ResolvedPropertyReflection($this->transformPropertyWithStaticType($this->resolvedDeclaringClass, $this->propertyReflection), $this->resolveTemplateTypeMapToBounds ? $templateTypeMap->resolveToBounds() : $templateTypeMap);
    }
    public function withFechedOnType(\PHPStan\Type\Type $type) : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        return new \PHPStan\Reflection\Type\CalledOnTypeUnresolvedPropertyPrototypeReflection($this->propertyReflection, $this->resolvedDeclaringClass, $this->resolveTemplateTypeMapToBounds, $type);
    }
    private function transformPropertyWithStaticType(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\PropertyReflection $property) : \PHPStan\Reflection\PropertyReflection
    {
        $readableType = $this->transformStaticType($property->getReadableType());
        $writableType = $this->transformStaticType($property->getWritableType());
        return new \PHPStan\Reflection\Dummy\ChangedTypePropertyReflection($declaringClass, $property, $readableType, $writableType);
    }
    private function transformStaticType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        $callback = $this->transformStaticTypeCallback;
        return $callback($type);
    }
}
