<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

/** @api */
interface ConstantReflection extends \PHPStan\Reflection\ClassMemberReflection, \PHPStan\Reflection\GlobalConstantReflection
{
    /**
     * @return mixed
     */
    public function getValue();
}
