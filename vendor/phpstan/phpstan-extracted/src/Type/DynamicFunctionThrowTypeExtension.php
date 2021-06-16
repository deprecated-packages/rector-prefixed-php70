<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
/** @api */
interface DynamicFunctionThrowTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool;
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $funcCall, \PHPStan\Analyser\Scope $scope);
}
