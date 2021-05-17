<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodPrototypeReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\BuiltinMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
class NativeMethodReflection implements \PHPStan\Reflection\MethodReflection
{
    /** @var \PHPStan\Reflection\ReflectionProvider */
    private $reflectionProvider;
    /** @var \PHPStan\Reflection\ClassReflection */
    private $declaringClass;
    /** @var BuiltinMethodReflection */
    private $reflection;
    /** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs[] */
    private $variants;
    /** @var TrinaryLogic */
    private $hasSideEffects;
    /** @var string|null */
    private $stubPhpDocString;
    /** @var Type|null */
    private $throwType;
    /**
     * @param \PHPStan\Reflection\ReflectionProvider $reflectionProvider
     * @param \PHPStan\Reflection\ClassReflection $declaringClass
     * @param BuiltinMethodReflection $reflection
     * @param \PHPStan\Reflection\ParametersAcceptorWithPhpDocs[] $variants
     * @param TrinaryLogic $hasSideEffects
     * @param string|null $stubPhpDocString
     * @param Type|null $throwType
     */
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\Php\BuiltinMethodReflection $reflection, array $variants, \PHPStan\TrinaryLogic $hasSideEffects, $stubPhpDocString, $throwType)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->declaringClass = $declaringClass;
        $this->reflection = $reflection;
        $this->variants = $variants;
        $this->hasSideEffects = $hasSideEffects;
        $this->stubPhpDocString = $stubPhpDocString;
        $this->throwType = $throwType;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return $this->reflection->isStatic();
    }
    public function isPrivate() : bool
    {
        return $this->reflection->isPrivate();
    }
    public function isPublic() : bool
    {
        return $this->reflection->isPublic();
    }
    public function isAbstract() : bool
    {
        return $this->reflection->isAbstract();
    }
    public function getPrototype() : \PHPStan\Reflection\ClassMemberReflection
    {
        try {
            $prototypeMethod = $this->reflection->getPrototype();
            $prototypeDeclaringClass = $this->reflectionProvider->getClass($prototypeMethod->getDeclaringClass()->getName());
            return new \PHPStan\Reflection\MethodPrototypeReflection($prototypeMethod->getName(), $prototypeDeclaringClass, $prototypeMethod->isStatic(), $prototypeMethod->isPrivate(), $prototypeMethod->isPublic(), $prototypeMethod->isAbstract(), $prototypeMethod->isFinal(), $prototypeDeclaringClass->getNativeMethod($prototypeMethod->getName())->getVariants());
        } catch (\ReflectionException $e) {
            return $this;
        }
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    /**
     * @return \PHPStan\Reflection\ParametersAcceptorWithPhpDocs[]
     */
    public function getVariants() : array
    {
        return $this->variants;
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return null;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->isDeprecated();
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->reflection->isFinal());
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        return $this->throwType;
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        $name = \strtolower($this->getName());
        $isVoid = $this->isVoid();
        if ($name !== '__construct' && $isVoid) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        return $this->hasSideEffects;
    }
    private function isVoid() : bool
    {
        foreach ($this->variants as $variant) {
            if (!$variant->getReturnType() instanceof \PHPStan\Type\VoidType) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        if ($this->stubPhpDocString !== null) {
            return $this->stubPhpDocString;
        }
        return $this->reflection->getDocComment();
    }
}
