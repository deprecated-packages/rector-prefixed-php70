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
use PHPStan\Type\Type;
class CallbackUnresolvedMethodPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
{
    /** @var MethodReflection */
    private $methodReflection;
    /** @var ClassReflection */
    private $resolvedDeclaringClass;
    /** @var bool */
    private $resolveTemplateTypeMapToBounds;
    /** @var callable(Type): Type */
    private $transformStaticTypeCallback;
    /** @var MethodReflection|null */
    private $transformedMethod = null;
    /** @var self|null */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    /**
     * @param MethodReflection $methodReflection
     * @param ClassReflection $resolvedDeclaringClass
     * @param bool $resolveTemplateTypeMapToBounds
     * @param callable(Type): Type $transformStaticTypeCallback
     */
    public function __construct(\PHPStan\Reflection\MethodReflection $methodReflection, \PHPStan\Reflection\ClassReflection $resolvedDeclaringClass, bool $resolveTemplateTypeMapToBounds, callable $transformStaticTypeCallback)
    {
        $this->methodReflection = $methodReflection;
        $this->resolvedDeclaringClass = $resolvedDeclaringClass;
        $this->resolveTemplateTypeMapToBounds = $resolveTemplateTypeMapToBounds;
        $this->transformStaticTypeCallback = $transformStaticTypeCallback;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->methodReflection, $this->resolvedDeclaringClass, \false, $this->transformStaticTypeCallback);
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
        return new \PHPStan\Reflection\Type\CalledOnTypeUnresolvedMethodPrototypeReflection($this->methodReflection, $this->resolvedDeclaringClass, $this->resolveTemplateTypeMapToBounds, $type);
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
        $callback = $this->transformStaticTypeCallback;
        return $callback($type);
    }
}
