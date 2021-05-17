<?php

declare (strict_types=1);
namespace PHPStan\Rules\Constants;

use PHPStan\Reflection\ConstantReflection;
interface AlwaysUsedClassConstantsExtension
{
    public function isAlwaysUsed(\PHPStan\Reflection\ConstantReflection $constant) : bool;
}
