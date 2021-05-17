<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\DynamicFunctionThrowTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
class JsonThrowTypeExtension implements \PHPStan\Type\DynamicFunctionThrowTypeExtension
{
    /** @var array<string, int> */
    private $argumentPositions = ['json_encode' => 1, 'json_decode' => 3];
    /** @var ReflectionProvider */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection) : bool
    {
        return $this->reflectionProvider->hasConstant(new \PhpParser\Node\Name\FullyQualified('JSON_THROW_ON_ERROR'), null) && \in_array($functionReflection->getName(), ['json_encode', 'json_decode'], \true);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope)
    {
        $argumentPosition = $this->argumentPositions[$functionReflection->getName()];
        if (!isset($functionCall->args[$argumentPosition])) {
            return null;
        }
        $optionsExpr = $functionCall->args[$argumentPosition]->value;
        if ($this->isBitwiseOrWithJsonThrowOnError($optionsExpr, $scope)) {
            return new \PHPStan\Type\ObjectType('JsonException');
        }
        $valueType = $scope->getType($optionsExpr);
        if (!$valueType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            return null;
        }
        $value = $valueType->getValue();
        $throwOnErrorType = $this->reflectionProvider->getConstant(new \PhpParser\Node\Name\FullyQualified('JSON_THROW_ON_ERROR'), null)->getValueType();
        if (!$throwOnErrorType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            return null;
        }
        $throwOnErrorValue = $throwOnErrorType->getValue();
        if (($value & $throwOnErrorValue) !== $throwOnErrorValue) {
            return null;
        }
        return new \PHPStan\Type\ObjectType('JsonException');
    }
    private function isBitwiseOrWithJsonThrowOnError(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            $constant = $this->reflectionProvider->resolveConstantName($expr->name, $scope);
            if ($constant === 'JSON_THROW_ON_ERROR') {
                return \true;
            }
        }
        if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            return \false;
        }
        return $this->isBitwiseOrWithJsonThrowOnError($expr->left, $scope) || $this->isBitwiseOrWithJsonThrowOnError($expr->right, $scope);
    }
}
