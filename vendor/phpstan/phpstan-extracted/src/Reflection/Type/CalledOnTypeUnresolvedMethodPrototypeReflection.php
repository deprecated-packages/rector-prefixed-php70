<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Dummy\ChangedTypeMethodReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Reflection\ResolvedMethodReflection;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
class CalledOnTypeUnresolvedMethodPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
{
    /** @var MethodReflection */
    private $methodReflection;
    /** @var ClassReflection */
    private $resolvedDeclaringClass;
    /** @var bool */
    private $resolveTemplateTypeMapToBounds;
    /** @var Type */
    private $calledOnType;
    /** @var MethodReflection|null */
    private $transformedMethod = null;
    /** @var self|null */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    public function __construct(\PHPStan\Reflection\MethodReflection $methodReflection, \PHPStan\Reflection\ClassReflection $resolvedDeclaringClass, bool $resolveTemplateTypeMapToBounds, \PHPStan\Type\Type $calledOnType)
    {
        $this->methodReflection = $methodReflection;
        $this->resolvedDeclaringClass = $resolvedDeclaringClass;
        $this->resolveTemplateTypeMapToBounds = $resolveTemplateTypeMapToBounds;
        $this->calledOnType = $calledOnType;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->methodReflection, $this->resolvedDeclaringClass, \false, $this->calledOnType);
    }
    public function getNakedMethod() : \PHPStan\Reflection\MethodReflection
    {
        return $this->methodReflection;
    }
    public function getTransformedMethod() : \PHPStan\Reflection\MethodReflection
    {
        if ($this->transformedMethod !== null) {
            return $this->transformedMethod;
        }
        $templateTypeMap = $this->resolvedDeclaringClass->getActiveTemplateTypeMap();
        return $this->transformedMethod = new \PHPStan\Reflection\ResolvedMethodReflection($this->transformMethodWithStaticType($this->resolvedDeclaringClass, $this->methodReflection), $this->resolveTemplateTypeMapToBounds ? $templateTypeMap->resolveToBounds() : $templateTypeMap);
    }
    public function withCalledOnType(\PHPStan\Type\Type $type) : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        return new self($this->methodReflection, $this->resolvedDeclaringClass, $this->resolveTemplateTypeMapToBounds, $type);
    }
    private function transformMethodWithStaticType(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\MethodReflection $method) : \PHPStan\Reflection\MethodReflection
    {
        $variants = \array_map(function (\PHPStan\Reflection\ParametersAcceptor $acceptor) : ParametersAcceptor {
            return new \PHPStan\Reflection\FunctionVariant($acceptor->getTemplateTypeMap(), $acceptor->getResolvedTemplateTypeMap(), \array_map(function (\PHPStan\Reflection\ParameterReflection $parameter) : ParameterReflection {
                return new \PHPStan\Reflection\Php\DummyParameter($parameter->getName(), $this->transformStaticType($parameter->getType()), $parameter->isOptional(), $parameter->passedByReference(), $parameter->isVariadic(), $parameter->getDefaultValue());
            }, $acceptor->getParameters()), $acceptor->isVariadic(), $this->transformStaticType($acceptor->getReturnType()));
        }, $method->getVariants());
        return new \PHPStan\Reflection\Dummy\ChangedTypeMethodReflection($declaringClass, $method, $variants);
    }
    private function transformStaticType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if ($type instanceof \PHPStan\Type\StaticType) {
                return $this->calledOnType;
            }
            return $traverse($type);
        });
    }
}
