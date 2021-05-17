<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Dummy;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
class DummyConstantReflection implements \PHPStan\Reflection\ConstantReflection
{
    /** @var string */
    private $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        $broker = \PHPStan\Broker\Broker::getInstance();
        return $broker->getClass(\stdClass::class);
    }
    /**
     * @return string|null
     */
    public function getFileName()
    {
        return null;
    }
    public function isStatic() : bool
    {
        return \true;
    }
    public function isPrivate() : bool
    {
        return \false;
    }
    public function isPublic() : bool
    {
        return \true;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        // so that Scope::getTypeFromValue() returns mixed
        return new \stdClass();
    }
    public function getValueType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\MixedType();
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return null;
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return null;
    }
}
