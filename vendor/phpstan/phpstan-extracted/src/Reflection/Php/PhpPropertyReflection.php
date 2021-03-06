<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
/** @api */
class PhpPropertyReflection implements \PHPStan\Reflection\PropertyReflection
{
    /** @var \PHPStan\Reflection\ClassReflection */
    private $declaringClass;
    /** @var \PHPStan\Reflection\ClassReflection|null */
    private $declaringTrait;
    /** @var \ReflectionType|null */
    private $nativeType;
    /** @var \PHPStan\Type\Type|null */
    private $finalNativeType = null;
    /** @var \PHPStan\Type\Type|null */
    private $phpDocType;
    /** @var \PHPStan\Type\Type|null */
    private $type = null;
    /** @var \ReflectionProperty */
    private $reflection;
    /** @var string|null */
    private $deprecatedDescription;
    /** @var bool */
    private $isDeprecated;
    /** @var bool */
    private $isInternal;
    /** @var string|null */
    private $stubPhpDocString;
    /**
     * @param \PHPStan\Reflection\ClassReflection|null $declaringTrait
     * @param \ReflectionType|null $nativeType
     * @param \PHPStan\Type\Type|null $phpDocType
     * @param string|null $deprecatedDescription
     * @param string|null $stubPhpDocString
     */
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringClass, $declaringTrait, $nativeType, $phpDocType, \ReflectionProperty $reflection, $deprecatedDescription, bool $isDeprecated, bool $isInternal, $stubPhpDocString)
    {
        $this->declaringClass = $declaringClass;
        $this->declaringTrait = $declaringTrait;
        $this->nativeType = $nativeType;
        $this->phpDocType = $phpDocType;
        $this->reflection = $reflection;
        $this->deprecatedDescription = $deprecatedDescription;
        $this->isDeprecated = $isDeprecated;
        $this->isInternal = $isInternal;
        $this->stubPhpDocString = $stubPhpDocString;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getDeclaringTrait()
    {
        return $this->declaringTrait;
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        if ($this->stubPhpDocString !== null) {
            return $this->stubPhpDocString;
        }
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
    public function getReadableType() : \PHPStan\Type\Type
    {
        if ($this->type === null) {
            $this->type = \PHPStan\Type\TypehintHelper::decideTypeFromReflection($this->nativeType, $this->phpDocType, $this->declaringClass->getName());
        }
        return $this->type;
    }
    public function getWritableType() : \PHPStan\Type\Type
    {
        return $this->getReadableType();
    }
    public function canChangeTypeAfterAssignment() : bool
    {
        return \true;
    }
    public function isPromoted() : bool
    {
        if (!\method_exists($this->reflection, 'isPromoted')) {
            return \false;
        }
        return $this->reflection->isPromoted();
    }
    public function hasPhpDoc() : bool
    {
        return $this->phpDocType !== null;
    }
    public function getPhpDocType() : \PHPStan\Type\Type
    {
        if ($this->phpDocType !== null) {
            return $this->phpDocType;
        }
        return new \PHPStan\Type\MixedType();
    }
    public function getNativeType() : \PHPStan\Type\Type
    {
        if ($this->finalNativeType === null) {
            $this->finalNativeType = \PHPStan\Type\TypehintHelper::decideTypeFromReflection($this->nativeType, null, $this->declaringClass->getName());
        }
        return $this->finalNativeType;
    }
    public function isReadable() : bool
    {
        return \true;
    }
    public function isWritable() : bool
    {
        return \true;
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
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isDeprecated);
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isInternal);
    }
    public function getNativeReflection() : \ReflectionProperty
    {
        return $this->reflection;
    }
}
