<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php\Soap;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
class SoapClientMethodReflection implements \PHPStan\Reflection\MethodReflection
{
    /** @var ClassReflection */
    private $declaringClass;
    /** @var string */
    private $name;
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringClass, string $name)
    {
        $this->declaringClass = $declaringClass;
        $this->name = $name;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return \false;
    }
    public function isPrivate() : bool
    {
        return \false;
    }
    public function isPublic() : bool
    {
        return \true;
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return null;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getPrototype() : \PHPStan\Reflection\ClassMemberReflection
    {
        return $this;
    }
    public function getVariants() : array
    {
        return [new \PHPStan\Reflection\FunctionVariant(\PHPStan\Type\Generic\TemplateTypeMap::createEmpty(), \PHPStan\Type\Generic\TemplateTypeMap::createEmpty(), [], \true, new \PHPStan\Type\MixedType(\true))];
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return null;
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        return new \PHPStan\Type\ObjectType('SoapFault');
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createYes();
    }
}
