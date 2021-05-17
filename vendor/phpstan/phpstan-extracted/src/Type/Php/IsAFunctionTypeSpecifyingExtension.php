<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
class IsAFunctionTypeSpecifyingExtension implements \PHPStan\Type\FunctionTypeSpecifyingExtension, \PHPStan\Analyser\TypeSpecifierAwareExtension
{
    /** @var \PHPStan\Analyser\TypeSpecifier */
    private $typeSpecifier;
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\TypeSpecifierContext $context) : bool
    {
        return \strtolower($functionReflection->getName()) === 'is_a' && isset($node->args[0]) && isset($node->args[1]) && !$context->null();
    }
    public function specifyTypes(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        if ($context->null()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $classNameArgExpr = $node->args[1]->value;
        $classNameArgExprType = $scope->getType($classNameArgExpr);
        if ($classNameArgExpr instanceof \PhpParser\Node\Expr\ClassConstFetch && $classNameArgExpr->class instanceof \PhpParser\Node\Name && $classNameArgExpr->name instanceof \PhpParser\Node\Identifier && \strtolower($classNameArgExpr->name->name) === 'class') {
            $objectType = $scope->resolveTypeByName($classNameArgExpr->class);
            $types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context, \false, $scope);
        } elseif ($classNameArgExprType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            $objectType = new \PHPStan\Type\ObjectType($classNameArgExprType->getValue());
            $types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context, \false, $scope);
        } elseif ($classNameArgExprType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            $objectType = $classNameArgExprType->getGenericType();
            $types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context, \false, $scope);
        } elseif ($context->true()) {
            $objectType = new \PHPStan\Type\ObjectWithoutClassType();
            $types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context, \false, $scope);
        } else {
            $types = new \PHPStan\Analyser\SpecifiedTypes();
        }
        if (isset($node->args[2]) && $context->true()) {
            if (!$scope->getType($node->args[2]->value)->isSuperTypeOf(new \PHPStan\Type\Constant\ConstantBooleanType(\true))->no()) {
                $types = $types->intersectWith($this->typeSpecifier->create($node->args[0]->value, isset($objectType) ? new \PHPStan\Type\Generic\GenericClassStringType($objectType) : new \PHPStan\Type\ClassStringType(), $context, \false, $scope));
            }
        }
        return $types;
    }
    /**
     * @return void
     */
    public function setTypeSpecifier(\PHPStan\Analyser\TypeSpecifier $typeSpecifier)
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
