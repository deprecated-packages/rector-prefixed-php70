<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Dummy;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\WrapperPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
class ChangedTypePropertyReflection implements \PHPStan\Reflection\WrapperPropertyReflection
{
    /** @var ClassReflection */
    private $declaringClass;
    /** @var PropertyReflection */
    private $reflection;
    /** @var Type */
    private $readableType;
    /** @var Type */
    private $writableType;
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\PropertyReflection $reflection, \PHPStan\Type\Type $readableType, \PHPStan\Type\Type $writableType)
    {
        $this->declaringClass = $declaringClass;
        $this->reflection = $reflection;
        $this->readableType = $readableType;
        $this->writableType = $writableType;
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
    public function getReadableType() : \PHPStan\Type\Type
    {
        return $this->readableType;
    }
    public function getWritableType() : \PHPStan\Type\Type
    {
        return $this->writableType;
    }
    public function canChangeTypeAfterAssignment() : bool
    {
        return $this->reflection->canChangeTypeAfterAssignment();
    }
    public function isReadable() : bool
    {
        return $this->reflection->isReadable();
    }
    public function isWritable() : bool
    {
        return $this->reflection->isWritable();
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
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return $this->reflection->isInternal();
    }
    public function getOriginalReflection() : \PHPStan\Reflection\PropertyReflection
    {
        return $this->reflection;
    }
}
