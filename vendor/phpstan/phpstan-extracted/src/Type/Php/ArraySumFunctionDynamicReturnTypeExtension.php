<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
final class ArraySumFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'array_sum';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        if (!isset($functionCall->args[0])) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }
        $arrayType = $scope->getType($functionCall->args[0]->value);
        $itemType = $arrayType->getIterableValueType();
        if ($arrayType->isIterableAtLeastOnce()->no()) {
            return new \PHPStan\Type\Constant\ConstantIntegerType(0);
        }
        $intUnionFloat = new \PHPStan\Type\UnionType([new \PHPStan\Type\IntegerType(), new \PHPStan\Type\FloatType()]);
        if ($arrayType->isIterableAtLeastOnce()->yes()) {
            if ($intUnionFloat->isSuperTypeOf($itemType)->yes()) {
                return $itemType;
            }
            return $intUnionFloat;
        }
        if ($intUnionFloat->isSuperTypeOf($itemType)->yes()) {
            return \PHPStan\Type\TypeCombinator::union(new \PHPStan\Type\Constant\ConstantIntegerType(0), $itemType);
        }
        return \PHPStan\Type\TypeCombinator::union(new \PHPStan\Type\Constant\ConstantIntegerType(0), $intUnionFloat);
    }
}
