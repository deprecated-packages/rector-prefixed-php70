<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\TrinaryLogic;
class NativeBuiltinMethodReflection implements \PHPStan\Reflection\Php\BuiltinMethodReflection
{
    /** @var \ReflectionMethod */
    private $reflection;
    public function __construct(\ReflectionMethod $reflection)
    {
        $this->reflection = $reflection;
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    /**
     * @return \ReflectionMethod|null
     */
    public function getReflection()
    {
        return $this->reflection;
    }
    /**
     * @return string|false
     */
    public function getFileName()
    {
        return $this->reflection->getFileName();
    }
    public function getDeclaringClass() : \ReflectionClass
    {
        return $this->reflection->getDeclaringClass();
    }
    /**
     * @return int|false
     */
    public function getStartLine()
    {
        return $this->reflection->getStartLine();
    }
    /**
     * @return int|false
     */
    public function getEndLine()
    {
        return $this->reflection->getEndLine();
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        $docComment = $this->reflection->getDocComment();
        if ($docComment === \false) {
            return null;
        }
        return $docComment;
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
    public function isConstructor() : bool
    {
        return $this->reflection->isConstructor();
    }
    public function getPrototype() : \PHPStan\Reflection\Php\BuiltinMethodReflection
    {
        return new self($this->reflection->getPrototype());
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->reflection->isDeprecated());
    }
    public function isFinal() : bool
    {
        return $this->reflection->isFinal();
    }
    public function isInternal() : bool
    {
        return $this->reflection->isInternal();
    }
    public function isAbstract() : bool
    {
        return $this->reflection->isAbstract();
    }
    public function isVariadic() : bool
    {
        return $this->reflection->isVariadic();
    }
    /**
     * @return \ReflectionType|null
     */
    public function getReturnType()
    {
        return $this->reflection->getReturnType();
    }
    /**
     * @return \ReflectionParameter[]
     */
    public function getParameters() : array
    {
        return $this->reflection->getParameters();
    }
}
