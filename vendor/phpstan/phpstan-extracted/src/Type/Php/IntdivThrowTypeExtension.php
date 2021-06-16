<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionThrowTypeExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
class IntdivThrowTypeExtension implements \PHPStan\Type\DynamicFunctionThrowTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'intdiv';
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $funcCall, \PHPStan\Analyser\Scope $scope)
    {
        if (\count($funcCall->args) < 2) {
            return $functionReflection->getThrowType();
        }
        $containsMin = \false;
        $valueType = $scope->getType($funcCall->args[0]->value);
        foreach (\PHPStan\Type\TypeUtils::getConstantScalars($valueType) as $constantScalarType) {
            if ($constantScalarType->getValue() === \PHP_INT_MIN) {
                $containsMin = \true;
            }
            $valueType = \PHPStan\Type\TypeCombinator::remove($valueType, $constantScalarType);
        }
        if (!$valueType instanceof \PHPStan\Type\NeverType) {
            $containsMin = \true;
        }
        $divisionByZero = \false;
        $divisorType = $scope->getType($funcCall->args[1]->value);
        foreach (\PHPStan\Type\TypeUtils::getConstantScalars($divisorType) as $constantScalarType) {
            if ($containsMin && $constantScalarType->getValue() === -1) {
                return new \PHPStan\Type\ObjectType(\ArithmeticError::class);
            }
            if ($constantScalarType->getValue() === 0) {
                $divisionByZero = \true;
            }
            $divisorType = \PHPStan\Type\TypeCombinator::remove($divisorType, $constantScalarType);
        }
        if (!$divisorType instanceof \PHPStan\Type\NeverType) {
            return new \PHPStan\Type\ObjectType($containsMin ? \ArithmeticError::class : \DivisionByZeroError::class);
        }
        if ($divisionByZero) {
            return new \PHPStan\Type\ObjectType(\DivisionByZeroError::class);
        }
        return null;
    }
}
