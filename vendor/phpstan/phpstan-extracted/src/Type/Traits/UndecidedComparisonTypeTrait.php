<?php

declare (strict_types=1);
namespace PHPStan\Type\Traits;

use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
trait UndecidedComparisonTypeTrait
{
    public function isSmallerThan(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    public function isSmallerThanOrEqual(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    public function getSmallerType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\MixedType();
    }
    public function getSmallerOrEqualType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\MixedType();
    }
    public function getGreaterType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\MixedType();
    }
    public function getGreaterOrEqualType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\MixedType();
    }
}
