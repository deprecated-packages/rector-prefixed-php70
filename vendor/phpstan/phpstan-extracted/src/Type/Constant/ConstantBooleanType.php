<?php

declare (strict_types=1);
namespace PHPStan\Type\Constant;

use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StaticTypeFactory;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
class ConstantBooleanType extends \PHPStan\Type\BooleanType implements \PHPStan\Type\ConstantScalarType
{
    use ConstantScalarTypeTrait;
    /** @var bool */
    private $value;
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
    public function getValue() : bool
    {
        return $this->value;
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return $this->value ? 'true' : 'false';
    }
    public function getSmallerType() : \PHPStan\Type\Type
    {
        if ($this->value) {
            return \PHPStan\Type\StaticTypeFactory::falsey();
        }
        return new \PHPStan\Type\NeverType();
    }
    public function getSmallerOrEqualType() : \PHPStan\Type\Type
    {
        if ($this->value) {
            return new \PHPStan\Type\MixedType();
        }
        return \PHPStan\Type\StaticTypeFactory::falsey();
    }
    public function getGreaterType() : \PHPStan\Type\Type
    {
        if ($this->value) {
            return new \PHPStan\Type\NeverType();
        }
        return \PHPStan\Type\StaticTypeFactory::truthy();
    }
    public function getGreaterOrEqualType() : \PHPStan\Type\Type
    {
        if ($this->value) {
            return \PHPStan\Type\StaticTypeFactory::truthy();
        }
        return new \PHPStan\Type\MixedType();
    }
    public function toBoolean() : \PHPStan\Type\BooleanType
    {
        return $this;
    }
    public function toNumber() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantIntegerType((int) $this->value);
    }
    public function toString() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantStringType((string) $this->value);
    }
    public function toInteger() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantIntegerType((int) $this->value);
    }
    public function toFloat() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantFloatType((float) $this->value);
    }
    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['value']);
    }
}
