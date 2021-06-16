<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
final class NumberFormatFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'number_format';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $stringType = new \PHPStan\Type\StringType();
        if (!isset($functionCall->args[3])) {
            return $stringType;
        }
        $thousandsType = $scope->getType($functionCall->args[3]->value);
        $decimalType = $scope->getType($functionCall->args[2]->value);
        if (!$thousandsType instanceof \PHPStan\Type\Constant\ConstantStringType || $thousandsType->getValue() !== '') {
            return $stringType;
        }
        if (!$decimalType instanceof \PHPStan\Type\ConstantScalarType || !\in_array($decimalType->getValue(), [null, '.', ''], \true)) {
            return $stringType;
        }
        return new \PHPStan\Type\IntersectionType([$stringType, new \PHPStan\Type\Accessory\AccessoryNumericStringType()]);
    }
}
