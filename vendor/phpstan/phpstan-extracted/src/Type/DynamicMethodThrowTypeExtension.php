<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
interface DynamicMethodThrowTypeExtension
{
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool;
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope);
}
