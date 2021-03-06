<?php

declare (strict_types=1);
namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Traits\ConstantNumericComparisonTypeTrait;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
/** @api */
class ConstantIntegerType extends \PHPStan\Type\IntegerType implements \PHPStan\Type\ConstantScalarType
{
    use ConstantScalarTypeTrait;
    use ConstantScalarToBooleanTrait;
    use ConstantNumericComparisonTypeTrait;
    /** @var int */
    private $value;
    /** @api */
    public function __construct(int $value)
    {
        parent::__construct();
        $this->value = $value;
    }
    public function getValue() : int
    {
        return $this->value;
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof self) {
            return $this->value === $type->value ? \PHPStan\TrinaryLogic::createYes() : \PHPStan\TrinaryLogic::createNo();
        }
        if ($type instanceof \PHPStan\Type\IntegerRangeType) {
            $min = $type->getMin();
            $max = $type->getMax();
            if (($min === null || $min <= $this->value) && ($max === null || $this->value <= $max)) {
                return \PHPStan\TrinaryLogic::createMaybe();
            }
            return \PHPStan\TrinaryLogic::createNo();
        }
        if ($type instanceof parent) {
            return \PHPStan\TrinaryLogic::createMaybe();
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOf($this);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return $level->handle(static function () : string {
            return 'int';
        }, function () : string {
            return \sprintf('%s', $this->value);
        });
    }
    public function toFloat() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantFloatType($this->value);
    }
    public function toString() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\Constant\ConstantStringType((string) $this->value);
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
