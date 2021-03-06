<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\Type;
class ClassConstantReflection implements \PHPStan\Reflection\ConstantReflection
{
    /** @var \PHPStan\Reflection\ClassReflection */
    private $declaringClass;
    /** @var \ReflectionClassConstant */
    private $reflection;
    /** @var string|null */
    private $deprecatedDescription;
    /** @var bool */
    private $isDeprecated;
    /** @var bool */
    private $isInternal;
    /** @var Type|null */
    private $valueType = null;
    /**
     * @param string|null $deprecatedDescription
     */
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringClass, \ReflectionClassConstant $reflection, $deprecatedDescription, bool $isDeprecated, bool $isInternal)
    {
        $this->declaringClass = $declaringClass;
        $this->reflection = $reflection;
        $this->deprecatedDescription = $deprecatedDescription;
        $this->isDeprecated = $isDeprecated;
        $this->isInternal = $isInternal;
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    /**
     * @return string|null
     */
    public function getFileName()
    {
        $fileName = $this->declaringClass->getFileName();
        if ($fileName === \false) {
            return null;
        }
        return $fileName;
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->reflection->getValue();
    }
    public function getValueType() : \PHPStan\Type\Type
    {
        if ($this->valueType === null) {
            $this->valueType = \PHPStan\Type\ConstantTypeHelper::getTypeFromValue($this->getValue());
        }
        return $this->valueType;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return \true;
    }
    public function isPrivate() : bool
    {
        return $this->reflection->isPrivate();
    }
    public function isPublic() : bool
    {
        return $this->reflection->isPublic();
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isDeprecated);
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        if ($this->isDeprecated) {
            return $this->deprecatedDescription;
        }
        return null;
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isInternal);
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
}
