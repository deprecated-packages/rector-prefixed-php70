<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
class ArrayCombineFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    /** @var PhpVersion */
    private $phpVersion;
    public function __construct(\PHPStan\Php\PhpVersion $phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'array_combine';
    }
    public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        if (\count($functionCall->args) < 2) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }
        $firstArg = $functionCall->args[0]->value;
        $secondArg = $functionCall->args[1]->value;
        $keysParamType = $scope->getType($firstArg);
        $valuesParamType = $scope->getType($secondArg);
        if ($keysParamType instanceof \PHPStan\Type\Constant\ConstantArrayType && $valuesParamType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            $keyTypes = $keysParamType->getValueTypes();
            $valueTypes = $valuesParamType->getValueTypes();
            if (\count($keyTypes) !== \count($valueTypes)) {
                return new \PHPStan\Type\Constant\ConstantBooleanType(\false);
            }
            $keyTypes = $this->sanitizeConstantArrayKeyTypes($keyTypes);
            if ($keyTypes !== null) {
                return new \PHPStan\Type\Constant\ConstantArrayType($keyTypes, $valueTypes);
            }
        }
        $arrayType = new \PHPStan\Type\ArrayType($keysParamType instanceof \PHPStan\Type\ArrayType ? $keysParamType->getItemType() : new \PHPStan\Type\MixedType(), $valuesParamType instanceof \PHPStan\Type\ArrayType ? $valuesParamType->getItemType() : new \PHPStan\Type\MixedType());
        if ($this->phpVersion->throwsTypeErrorForInternalFunctions()) {
            return $arrayType;
        }
        if ($firstArg instanceof \PhpParser\Node\Expr\Variable && $secondArg instanceof \PhpParser\Node\Expr\Variable && $firstArg->name === $secondArg->name) {
            return $arrayType;
        }
        return new \PHPStan\Type\UnionType([$arrayType, new \PHPStan\Type\Constant\ConstantBooleanType(\false)]);
    }
    /**
     * @param array<int, Type> $types
     *
     * @return mixed[]|null
     */
    private function sanitizeConstantArrayKeyTypes(array $types)
    {
        $sanitizedTypes = [];
        foreach ($types as $type) {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantIntegerType && !$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return null;
            }
            $sanitizedTypes[] = $type;
        }
        return $sanitizedTypes;
    }
}
