<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
class RandomIntFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'random_int';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        if (\count($functionCall->args) < 2) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }
        $minType = $scope->getType($functionCall->args[0]->value)->toInteger();
        $maxType = $scope->getType($functionCall->args[1]->value)->toInteger();
        return $this->createRange($minType, $maxType);
    }
    private function createRange(\PHPStan\Type\Type $minType, \PHPStan\Type\Type $maxType) : \PHPStan\Type\Type
    {
        $minValues = \array_map(static function (\PHPStan\Type\Type $type) {
            if ($type instanceof \PHPStan\Type\IntegerRangeType) {
                return $type->getMin();
            }
            if ($type instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                return $type->getValue();
            }
            return null;
        }, $minType instanceof \PHPStan\Type\UnionType ? $minType->getTypes() : [$minType]);
        $maxValues = \array_map(static function (\PHPStan\Type\Type $type) {
            if ($type instanceof \PHPStan\Type\IntegerRangeType) {
                return $type->getMax();
            }
            if ($type instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                return $type->getValue();
            }
            return null;
        }, $maxType instanceof \PHPStan\Type\UnionType ? $maxType->getTypes() : [$maxType]);
        \assert(\count($minValues) > 0);
        \assert(\count($maxValues) > 0);
        return \PHPStan\Type\IntegerRangeType::fromInterval(\in_array(null, $minValues, \true) ? null : \min($minValues), \in_array(null, $maxValues, \true) ? null : \max($maxValues));
    }
}
