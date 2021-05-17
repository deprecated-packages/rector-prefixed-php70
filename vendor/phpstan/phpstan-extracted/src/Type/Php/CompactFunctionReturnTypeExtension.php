<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;
class CompactFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    /** @var bool */
    private $checkMaybeUndefinedVariables;
    public function __construct(bool $checkMaybeUndefinedVariables)
    {
        $this->checkMaybeUndefinedVariables = $checkMaybeUndefinedVariables;
    }
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'compact';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $defaultReturnType = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        if (\count($functionCall->args) === 0) {
            return $defaultReturnType;
        }
        if ($scope->canAnyVariableExist() && !$this->checkMaybeUndefinedVariables) {
            return $defaultReturnType;
        }
        $array = \PHPStan\Type\Constant\ConstantArrayTypeBuilder::createEmpty();
        foreach ($functionCall->args as $arg) {
            $type = $scope->getType($arg->value);
            $constantStrings = $this->findConstantStrings($type);
            if ($constantStrings === null) {
                return $defaultReturnType;
            }
            foreach ($constantStrings as $constantString) {
                $has = $scope->hasVariableType($constantString->getValue());
                if ($has->no()) {
                    continue;
                }
                $array->setOffsetValueType($constantString, $scope->getVariableType($constantString->getValue()), $has->maybe());
            }
        }
        return $array->getArray();
    }
    /**
     * @param Type $type
     * @return mixed[]|null
     */
    private function findConstantStrings(\PHPStan\Type\Type $type)
    {
        if ($type instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return [$type];
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            $result = [];
            foreach ($type->getValueTypes() as $valueType) {
                $constantStrings = $this->findConstantStrings($valueType);
                if ($constantStrings === null) {
                    return null;
                }
                $result = \array_merge($result, $constantStrings);
            }
            return $result;
        }
        return null;
    }
}
