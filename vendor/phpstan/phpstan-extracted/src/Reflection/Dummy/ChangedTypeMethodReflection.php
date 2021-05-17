<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Dummy;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
class ChangedTypeMethodReflection implements \PHPStan\Reflection\MethodReflection
{
    /** @var ClassReflection */
    private $declaringClass;
    /** @var MethodReflection */
    private $reflection;
    /** @var ParametersAcceptor[] */
    private $variants;
    /**
     * @param MethodReflection $reflection
     * @param ParametersAcceptor[] $variants
     */
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\MethodReflection $reflection, array $variants)
    {
        $this->declaringClass = $declaringClass;
        $this->reflection = $reflection;
        $this->variants = $variants;
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
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return $this->reflection->getDocComment();
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    public function getPrototype() : \PHPStan\Reflection\ClassMemberReflection
    {
        return $this->reflection->getPrototype();
    }
    public function getVariants() : array
    {
        return $this->variants;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->isDeprecated();
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return $this->reflection->getDeprecatedDescription();
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->isFinal();
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->isInternal();
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        return $this->reflection->getThrowType();
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->hasSideEffects();
    }
}
