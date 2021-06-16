<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
/** @api */
interface DynamicStaticMethodThrowTypeExtension
{
    public function isStaticMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool;
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromStaticMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\StaticCall $methodCall, \PHPStan\Analyser\Scope $scope);
}
