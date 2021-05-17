<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Dummy\ChangedTypePropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ResolvedPropertyReflection;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
class CalledOnTypeUnresolvedPropertyPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
{
    /** @var PropertyReflection */
    private $propertyReflection;
    /** @var ClassReflection */
    private $resolvedDeclaringClass;
    /** @var bool */
    private $resolveTemplateTypeMapToBounds;
    /** @var Type */
    private $fetchedOnType;
    /** @var PropertyReflection|null */
    private $transformedProperty = null;
    /** @var self|null */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    public function __construct(\PHPStan\Reflection\PropertyReflection $propertyReflection, \PHPStan\Reflection\ClassReflection $resolvedDeclaringClass, bool $resolveTemplateTypeMapToBounds, \PHPStan\Type\Type $fetchedOnType)
    {
        $this->propertyReflection = $propertyReflection;
        $this->resolvedDeclaringClass = $resolvedDeclaringClass;
        $this->resolveTemplateTypeMapToBounds = $resolveTemplateTypeMapToBounds;
        $this->fetchedOnType = $fetchedOnType;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->propertyReflection, $this->resolvedDeclaringClass, \false, $this->fetchedOnType);
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
        return new self($this->propertyReflection, $this->resolvedDeclaringClass, $this->resolveTemplateTypeMapToBounds, $type);
    }
    private function transformPropertyWithStaticType(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\PropertyReflection $property) : \PHPStan\Reflection\PropertyReflection
    {
        $readableType = $this->transformStaticType($property->getReadableType());
        $writableType = $this->transformStaticType($property->getWritableType());
        return new \PHPStan\Reflection\Dummy\ChangedTypePropertyReflection($declaringClass, $property, $readableType, $writableType);
    }
    private function transformStaticType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if ($type instanceof \PHPStan\Type\StaticType) {
                return $this->fetchedOnType;
            }
            return $traverse($type);
        });
    }
}
