<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
/** @api */
interface CompoundType extends \PHPStan\Type\Type
{
    public function isSubTypeOf(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic;
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\TrinaryLogic;
    public function isGreaterThan(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic;
    public function isGreaterThanOrEqual(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic;
}
