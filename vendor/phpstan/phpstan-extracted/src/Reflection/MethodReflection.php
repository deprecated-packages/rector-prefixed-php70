<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
/** @api */
interface MethodReflection extends \PHPStan\Reflection\ClassMemberReflection
{
    public function getName() : string;
    public function getPrototype() : \PHPStan\Reflection\ClassMemberReflection;
    /**
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getVariants() : array;
    public function isDeprecated() : \PHPStan\TrinaryLogic;
    /**
     * @return string|null
     */
    public function getDeprecatedDescription();
    public function isFinal() : \PHPStan\TrinaryLogic;
    public function isInternal() : \PHPStan\TrinaryLogic;
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType();
    public function hasSideEffects() : \PHPStan\TrinaryLogic;
}
