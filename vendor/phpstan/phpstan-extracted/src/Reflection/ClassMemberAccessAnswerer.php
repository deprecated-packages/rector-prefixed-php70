<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

interface ClassMemberAccessAnswerer
{
    public function isInClass() : bool;
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getClassReflection();
    public function canAccessProperty(\PHPStan\Reflection\PropertyReflection $propertyReflection) : bool;
    public function canCallMethod(\PHPStan\Reflection\MethodReflection $methodReflection) : bool;
    public function canAccessConstant(\PHPStan\Reflection\ConstantReflection $constantReflection) : bool;
}
