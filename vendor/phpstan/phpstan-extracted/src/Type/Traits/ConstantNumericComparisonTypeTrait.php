<?php

declare (strict_types=1);
namespace PHPStan\Type\Traits;

use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
trait ConstantNumericComparisonTypeTrait
{
    public function getSmallerType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [new \PHPStan\Type\Constant\ConstantBooleanType(\true), \PHPStan\Type\IntegerRangeType::createAllGreaterThanOrEqualTo($this->value)];
        if (!(bool) $this->value) {
            $subtractedTypes[] = new \PHPStan\Type\NullType();
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\false);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getSmallerOrEqualType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [\PHPStan\Type\IntegerRangeType::createAllGreaterThan($this->value)];
        if (!(bool) $this->value) {
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\true);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getGreaterType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [new \PHPStan\Type\NullType(), new \PHPStan\Type\Constant\ConstantBooleanType(\false), \PHPStan\Type\IntegerRangeType::createAllSmallerThanOrEqualTo($this->value)];
        if ((bool) $this->value) {
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\true);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getGreaterOrEqualType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [\PHPStan\Type\IntegerRangeType::createAllSmallerThan($this->value)];
        if ((bool) $this->value) {
            $subtractedTypes[] = new \PHPStan\Type\NullType();
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\false);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
}
