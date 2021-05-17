<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php\Soap;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
class SoapClientMethodsClassReflectionExtension implements \PHPStan\Reflection\MethodsClassReflectionExtension
{
    public function hasMethod(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        return $classReflection->getName() === 'SoapClient' || $classReflection->isSubclassOf('SoapClient');
    }
    public function getMethod(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : \PHPStan\Reflection\MethodReflection
    {
        return new \PHPStan\Reflection\Php\Soap\SoapClientMethodReflection($classReflection, $methodName);
    }
}
