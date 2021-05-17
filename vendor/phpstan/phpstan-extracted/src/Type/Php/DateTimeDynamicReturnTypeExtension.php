<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use DateTime;
use DateTimeImmutable;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
class DateTimeDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return \in_array($functionReflection->getName(), ['date_create_from_format', 'date_create_immutable_from_format'], \true);
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $defaultReturnType = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        if (\count($functionCall->args) < 2) {
            return $defaultReturnType;
        }
        $format = $scope->getType($functionCall->args[0]->value);
        $datetime = $scope->getType($functionCall->args[1]->value);
        if (!$format instanceof \PHPStan\Type\Constant\ConstantStringType || !$datetime instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return $defaultReturnType;
        }
        $isValid = \DateTime::createFromFormat($format->getValue(), $datetime->getValue()) !== \false;
        $className = $functionReflection->getName() === 'date_create_from_format' ? \DateTime::class : \DateTimeImmutable::class;
        return $isValid ? new \PHPStan\Type\ObjectType($className) : new \PHPStan\Type\Constant\ConstantBooleanType(\false);
    }
}
