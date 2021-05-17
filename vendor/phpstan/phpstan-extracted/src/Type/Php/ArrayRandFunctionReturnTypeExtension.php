<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
class ArrayRandFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'array_rand';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $argsCount = \count($functionCall->args);
        if (\count($functionCall->args) < 1) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }
        $firstArgType = $scope->getType($functionCall->args[0]->value);
        $isInteger = (new \PHPStan\Type\IntegerType())->isSuperTypeOf($firstArgType->getIterableKeyType());
        $isString = (new \PHPStan\Type\StringType())->isSuperTypeOf($firstArgType->getIterableKeyType());
        if ($isInteger->yes()) {
            $valueType = new \PHPStan\Type\IntegerType();
        } elseif ($isString->yes()) {
            $valueType = new \PHPStan\Type\StringType();
        } else {
            $valueType = new \PHPStan\Type\UnionType([new \PHPStan\Type\IntegerType(), new \PHPStan\Type\StringType()]);
        }
        if ($argsCount < 2) {
            return $valueType;
        }
        $secondArgType = $scope->getType($functionCall->args[1]->value);
        if ($secondArgType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            if ($secondArgType->getValue() === 1) {
                return $valueType;
            }
            if ($secondArgType->getValue() >= 2) {
                return new \PHPStan\Type\ArrayType(new \PHPStan\Type\IntegerType(), $valueType);
            }
        }
        return \PHPStan\Type\TypeCombinator::union($valueType, new \PHPStan\Type\ArrayType(new \PHPStan\Type\IntegerType(), $valueType));
    }
}
