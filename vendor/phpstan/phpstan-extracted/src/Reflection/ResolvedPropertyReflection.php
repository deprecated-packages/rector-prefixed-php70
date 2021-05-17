<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
class ResolvedPropertyReflection implements \PHPStan\Reflection\WrapperPropertyReflection
{
    /** @var PropertyReflection */
    private $reflection;
    /** @var TemplateTypeMap */
    private $templateTypeMap;
    /** @var Type|null */
    private $readableType = null;
    /** @var Type|null */
    private $writableType = null;
    public function __construct(\PHPStan\Reflection\PropertyReflection $reflection, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap)
    {
        $this->reflection = $reflection;
        $this->templateTypeMap = $templateTypeMap;
    }
    public function getOriginalReflection() : \PHPStan\Reflection\PropertyReflection
    {
        return $this->reflection;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->reflection->getDeclaringClass();
    }
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getDeclaringTrait()
    {
        if ($this->reflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            return $this->reflection->getDeclaringTrait();
        }
        return null;
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
        $type = $this->readableType;
        if ($type !== null) {
            return $type;
        }
        $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($this->reflection->getReadableType(), $this->templateTypeMap);
        $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($type, $this->templateTypeMap);
        $this->readableType = $type;
        return $type;
    }
    public function getWritableType() : \PHPStan\Type\Type
    {
        $type = $this->writableType;
        if ($type !== null) {
            return $type;
        }
        $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($this->reflection->getWritableType(), $this->templateTypeMap);
        $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($type, $this->templateTypeMap);
        $this->writableType = $type;
        return $type;
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
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return $this->reflection->getDocComment();
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
}
