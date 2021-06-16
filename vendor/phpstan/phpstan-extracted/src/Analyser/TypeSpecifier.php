<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\HasPropertyType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StaticTypeFactory;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use function array_reverse;
class TypeSpecifier
{
    /** @var \PhpParser\PrettyPrinter\Standard */
    private $printer;
    /** @var ReflectionProvider */
    private $reflectionProvider;
    /** @var bool */
    private $rememberFunctionValues;
    /** @var \PHPStan\Type\FunctionTypeSpecifyingExtension[] */
    private $functionTypeSpecifyingExtensions;
    /** @var \PHPStan\Type\MethodTypeSpecifyingExtension[] */
    private $methodTypeSpecifyingExtensions;
    /** @var \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] */
    private $staticMethodTypeSpecifyingExtensions;
    /** @var \PHPStan\Type\MethodTypeSpecifyingExtension[][]|null */
    private $methodTypeSpecifyingExtensionsByClass = null;
    /** @var \PHPStan\Type\StaticMethodTypeSpecifyingExtension[][]|null */
    private $staticMethodTypeSpecifyingExtensionsByClass = null;
    /**
     * @param \PhpParser\PrettyPrinter\Standard $printer
     * @param ReflectionProvider $reflectionProvider
     * @param \PHPStan\Type\FunctionTypeSpecifyingExtension[] $functionTypeSpecifyingExtensions
     * @param \PHPStan\Type\MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
     * @param \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
     */
    public function __construct(\PhpParser\PrettyPrinter\Standard $printer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, bool $rememberFunctionValues, array $functionTypeSpecifyingExtensions, array $methodTypeSpecifyingExtensions, array $staticMethodTypeSpecifyingExtensions)
    {
        $this->printer = $printer;
        $this->reflectionProvider = $reflectionProvider;
        $this->rememberFunctionValues = $rememberFunctionValues;
        foreach (\array_merge($functionTypeSpecifyingExtensions, $methodTypeSpecifyingExtensions, $staticMethodTypeSpecifyingExtensions) as $extension) {
            if (!$extension instanceof \PHPStan\Analyser\TypeSpecifierAwareExtension) {
                continue;
            }
            $extension->setTypeSpecifier($this);
        }
        $this->functionTypeSpecifyingExtensions = $functionTypeSpecifyingExtensions;
        $this->methodTypeSpecifyingExtensions = $methodTypeSpecifyingExtensions;
        $this->staticMethodTypeSpecifyingExtensions = $staticMethodTypeSpecifyingExtensions;
    }
    /** @api */
    public function specifyTypesInCondition(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $expr, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        if ($expr instanceof \PhpParser\Node\Expr\Instanceof_) {
            $exprNode = $expr->expr;
            if ($expr->class instanceof \PhpParser\Node\Name) {
                $className = (string) $expr->class;
                $lowercasedClassName = \strtolower($className);
                if ($lowercasedClassName === 'self' && $scope->isInClass()) {
                    $type = new \PHPStan\Type\ObjectType($scope->getClassReflection()->getName());
                } elseif ($lowercasedClassName === 'static' && $scope->isInClass()) {
                    $type = new \PHPStan\Type\StaticType($scope->getClassReflection());
                } elseif ($lowercasedClassName === 'parent') {
                    if ($scope->isInClass() && $scope->getClassReflection()->getParentClass() !== \false) {
                        $type = new \PHPStan\Type\ObjectType($scope->getClassReflection()->getParentClass()->getName());
                    } else {
                        $type = new \PHPStan\Type\NonexistentParentClassType();
                    }
                } else {
                    $type = new \PHPStan\Type\ObjectType($className);
                }
                return $this->create($exprNode, $type, $context, \false, $scope);
            }
            $classType = $scope->getType($expr->class);
            $type = \PHPStan\Type\TypeTraverser::map($classType, static function (\PHPStan\Type\Type $type, callable $traverse) : Type {
                if ($type instanceof \PHPStan\Type\UnionType || $type instanceof \PHPStan\Type\IntersectionType) {
                    return $traverse($type);
                }
                if ($type instanceof \PHPStan\Type\TypeWithClassName) {
                    return $type;
                }
                if ($type instanceof \PHPStan\Type\Generic\GenericClassStringType) {
                    return $type->getGenericType();
                }
                if ($type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                    return new \PHPStan\Type\ObjectType($type->getValue());
                }
                return new \PHPStan\Type\MixedType();
            });
            if (!$type->isSuperTypeOf(new \PHPStan\Type\MixedType())->yes()) {
                if ($context->true()) {
                    $type = \PHPStan\Type\TypeCombinator::intersect($type, new \PHPStan\Type\ObjectWithoutClassType());
                    return $this->create($exprNode, $type, $context, \false, $scope);
                } elseif ($context->false()) {
                    $exprType = $scope->getType($expr->expr);
                    if (!$type->isSuperTypeOf($exprType)->yes()) {
                        return $this->create($exprNode, $type, $context, \false, $scope);
                    }
                }
            }
            if ($context->true()) {
                return $this->create($exprNode, new \PHPStan\Type\ObjectWithoutClassType(), $context, \false, $scope);
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $expressions = $this->findTypeExpressionsFromBinaryOperation($scope, $expr);
            if ($expressions !== null) {
                /** @var Expr $exprNode */
                $exprNode = $expressions[0];
                /** @var \PHPStan\Type\ConstantScalarType $constantType */
                $constantType = $expressions[1];
                if ($constantType->getValue() === \false) {
                    $types = $this->create($exprNode, $constantType, $context, \false, $scope);
                    return $types->unionWith($this->specifyTypesInCondition($scope, $exprNode, $context->true() ? \PHPStan\Analyser\TypeSpecifierContext::createFalse() : \PHPStan\Analyser\TypeSpecifierContext::createFalse()->negate()));
                }
                if ($constantType->getValue() === \true) {
                    $types = $this->create($exprNode, $constantType, $context, \false, $scope);
                    return $types->unionWith($this->specifyTypesInCondition($scope, $exprNode, $context->true() ? \PHPStan\Analyser\TypeSpecifierContext::createTrue() : \PHPStan\Analyser\TypeSpecifierContext::createTrue()->negate()));
                }
                if ($constantType->getValue() === null) {
                    return $this->create($exprNode, $constantType, $context, \false, $scope);
                }
                if (!$context->null() && $exprNode instanceof \PhpParser\Node\Expr\FuncCall && \count($exprNode->args) === 1 && $exprNode->name instanceof \PhpParser\Node\Name && \strtolower((string) $exprNode->name) === 'count' && $constantType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                    if ($context->truthy() || $constantType->getValue() === 0) {
                        $newContext = $context;
                        if ($constantType->getValue() === 0) {
                            $newContext = $newContext->negate();
                        }
                        $argType = $scope->getType($exprNode->args[0]->value);
                        if ($argType->isArray()->yes()) {
                            return $this->create($exprNode->args[0]->value, new \PHPStan\Type\Accessory\NonEmptyArrayType(), $newContext, \false, $scope);
                        }
                    }
                }
            }
            if ($context->true()) {
                $type = \PHPStan\Type\TypeCombinator::intersect($scope->getType($expr->right), $scope->getType($expr->left));
                $leftTypes = $this->create($expr->left, $type, $context, \false, $scope);
                $rightTypes = $this->create($expr->right, $type, $context, \false, $scope);
                return $leftTypes->unionWith($rightTypes);
            } elseif ($context->false()) {
                $identicalType = $scope->getType($expr);
                if ($identicalType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                    $never = new \PHPStan\Type\NeverType();
                    $contextForTypes = $identicalType->getValue() ? $context->negate() : $context;
                    $leftTypes = $this->create($expr->left, $never, $contextForTypes, \false, $scope);
                    $rightTypes = $this->create($expr->right, $never, $contextForTypes, \false, $scope);
                    return $leftTypes->unionWith($rightTypes);
                }
                $exprLeftType = $scope->getType($expr->left);
                $exprRightType = $scope->getType($expr->right);
                $types = null;
                if ($exprLeftType instanceof \PHPStan\Type\ConstantType && !$expr->right instanceof \PhpParser\Node\Scalar) {
                    $types = $this->create($expr->right, $exprLeftType, $context, \false, $scope);
                }
                if ($exprRightType instanceof \PHPStan\Type\ConstantType && !$expr->left instanceof \PhpParser\Node\Scalar) {
                    $leftType = $this->create($expr->left, $exprRightType, $context, \false, $scope);
                    if ($types !== null) {
                        $types = $types->unionWith($leftType);
                    } else {
                        $types = $leftType;
                    }
                }
                if ($types !== null) {
                    return $types;
                }
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\BinaryOp\Identical($expr->left, $expr->right)), $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Equal) {
            $expressions = $this->findTypeExpressionsFromBinaryOperation($scope, $expr);
            if ($expressions !== null) {
                /** @var Expr $exprNode */
                $exprNode = $expressions[0];
                /** @var \PHPStan\Type\ConstantScalarType $constantType */
                $constantType = $expressions[1];
                if ($constantType->getValue() === \false || $constantType->getValue() === null) {
                    return $this->specifyTypesInCondition($scope, $exprNode, $context->true() ? \PHPStan\Analyser\TypeSpecifierContext::createFalsey() : \PHPStan\Analyser\TypeSpecifierContext::createFalsey()->negate());
                }
                if ($constantType->getValue() === \true) {
                    return $this->specifyTypesInCondition($scope, $exprNode, $context->true() ? \PHPStan\Analyser\TypeSpecifierContext::createTruthy() : \PHPStan\Analyser\TypeSpecifierContext::createTruthy()->negate());
                }
            }
            $leftType = $scope->getType($expr->left);
            $leftBooleanType = $leftType->toBoolean();
            $rightType = $scope->getType($expr->right);
            if ($leftBooleanType instanceof \PHPStan\Type\Constant\ConstantBooleanType && $rightType instanceof \PHPStan\Type\BooleanType) {
                return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\Identical(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($leftBooleanType->getValue() ? 'true' : 'false')), $expr->right), $context);
            }
            $rightBooleanType = $rightType->toBoolean();
            if ($rightBooleanType instanceof \PHPStan\Type\Constant\ConstantBooleanType && $leftType instanceof \PHPStan\Type\BooleanType) {
                return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\Identical($expr->left, new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($rightBooleanType->getValue() ? 'true' : 'false'))), $context);
            }
            if ($expr->left instanceof \PhpParser\Node\Expr\FuncCall && $expr->left->name instanceof \PhpParser\Node\Name && \strtolower($expr->left->name->toString()) === 'get_class' && isset($expr->left->args[0]) && $rightType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\Instanceof_($expr->left->args[0]->value, new \PhpParser\Node\Name($rightType->getValue())), $context);
            }
            if ($expr->right instanceof \PhpParser\Node\Expr\FuncCall && $expr->right->name instanceof \PhpParser\Node\Name && \strtolower($expr->right->name->toString()) === 'get_class' && isset($expr->right->args[0]) && $leftType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\Instanceof_($expr->right->args[0]->value, new \PhpParser\Node\Name($leftType->getValue())), $context);
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual) {
            return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\BinaryOp\Equal($expr->left, $expr->right)), $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Smaller || $expr instanceof \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual) {
            $orEqual = $expr instanceof \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
            $offset = $orEqual ? 0 : 1;
            $leftType = $scope->getType($expr->left);
            $rightType = $scope->getType($expr->right);
            if ($expr->left instanceof \PhpParser\Node\Expr\FuncCall && \count($expr->left->args) === 1 && $expr->left->name instanceof \PhpParser\Node\Name && \strtolower((string) $expr->left->name) === 'count' && (!$expr->right instanceof \PhpParser\Node\Expr\FuncCall || !$expr->right->name instanceof \PhpParser\Node\Name || \strtolower((string) $expr->right->name) !== 'count')) {
                $inverseOperator = $expr instanceof \PhpParser\Node\Expr\BinaryOp\Smaller ? new \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual($expr->right, $expr->left) : new \PhpParser\Node\Expr\BinaryOp\Smaller($expr->right, $expr->left);
                return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BooleanNot($inverseOperator), $context);
            }
            $result = new \PHPStan\Analyser\SpecifiedTypes();
            if (!$context->null() && $expr->right instanceof \PhpParser\Node\Expr\FuncCall && \count($expr->right->args) === 1 && $expr->right->name instanceof \PhpParser\Node\Name && \strtolower((string) $expr->right->name) === 'count' && (new \PHPStan\Type\IntegerType())->isSuperTypeOf($leftType)->yes()) {
                if ($context->truthy() && \PHPStan\Type\IntegerRangeType::createAllGreaterThanOrEqualTo(1 - $offset)->isSuperTypeOf($leftType)->yes() || $context->falsey() && (new \PHPStan\Type\Constant\ConstantIntegerType(1 - $offset))->isSuperTypeOf($leftType)->yes()) {
                    $argType = $scope->getType($expr->right->args[0]->value);
                    if ($argType->isArray()->yes()) {
                        $result = $result->unionWith($this->create($expr->right->args[0]->value, new \PHPStan\Type\Accessory\NonEmptyArrayType(), $context, \false, $scope));
                    }
                }
            }
            if ($leftType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                if ($expr->right instanceof \PhpParser\Node\Expr\PostInc) {
                    $result = $result->unionWith($this->createRangeTypes($expr->right->var, \PHPStan\Type\IntegerRangeType::fromInterval($leftType->getValue(), null, $offset + 1), $context));
                } elseif ($expr->right instanceof \PhpParser\Node\Expr\PostDec) {
                    $result = $result->unionWith($this->createRangeTypes($expr->right->var, \PHPStan\Type\IntegerRangeType::fromInterval($leftType->getValue(), null, $offset - 1), $context));
                } elseif ($expr->right instanceof \PhpParser\Node\Expr\PreInc || $expr->right instanceof \PhpParser\Node\Expr\PreDec) {
                    $result = $result->unionWith($this->createRangeTypes($expr->right->var, \PHPStan\Type\IntegerRangeType::fromInterval($leftType->getValue(), null, $offset), $context));
                }
            }
            if ($rightType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                if ($expr->left instanceof \PhpParser\Node\Expr\PostInc) {
                    $result = $result->unionWith($this->createRangeTypes($expr->left->var, \PHPStan\Type\IntegerRangeType::fromInterval(null, $rightType->getValue(), -$offset + 1), $context));
                } elseif ($expr->left instanceof \PhpParser\Node\Expr\PostDec) {
                    $result = $result->unionWith($this->createRangeTypes($expr->left->var, \PHPStan\Type\IntegerRangeType::fromInterval(null, $rightType->getValue(), -$offset - 1), $context));
                } elseif ($expr->left instanceof \PhpParser\Node\Expr\PreInc || $expr->left instanceof \PhpParser\Node\Expr\PreDec) {
                    $result = $result->unionWith($this->createRangeTypes($expr->left->var, \PHPStan\Type\IntegerRangeType::fromInterval(null, $rightType->getValue(), -$offset), $context));
                }
            }
            if ($context->truthy()) {
                if (!$expr->left instanceof \PhpParser\Node\Scalar) {
                    $result = $result->unionWith($this->create($expr->left, $orEqual ? $rightType->getSmallerOrEqualType() : $rightType->getSmallerType(), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                }
                if (!$expr->right instanceof \PhpParser\Node\Scalar) {
                    $result = $result->unionWith($this->create($expr->right, $orEqual ? $leftType->getGreaterOrEqualType() : $leftType->getGreaterType(), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                }
            } elseif ($context->falsey()) {
                if (!$expr->left instanceof \PhpParser\Node\Scalar) {
                    $result = $result->unionWith($this->create($expr->left, $orEqual ? $rightType->getGreaterType() : $rightType->getGreaterOrEqualType(), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                }
                if (!$expr->right instanceof \PhpParser\Node\Scalar) {
                    $result = $result->unionWith($this->create($expr->right, $orEqual ? $leftType->getSmallerType() : $leftType->getSmallerOrEqualType(), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                }
            }
            return $result;
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Greater) {
            return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\Smaller($expr->right, $expr->left), $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual) {
            return $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual($expr->right, $expr->left), $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\FuncCall && $expr->name instanceof \PhpParser\Node\Name) {
            if ($this->reflectionProvider->hasFunction($expr->name, $scope)) {
                $functionReflection = $this->reflectionProvider->getFunction($expr->name, $scope);
                foreach ($this->getFunctionTypeSpecifyingExtensions() as $extension) {
                    if (!$extension->isFunctionSupported($functionReflection, $expr, $context)) {
                        continue;
                    }
                    return $extension->specifyTypes($functionReflection, $expr, $scope, $context);
                }
            }
            if ($this->rememberFunctionValues) {
                return $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\MethodCall && $expr->name instanceof \PhpParser\Node\Identifier) {
            $methodCalledOnType = $scope->getType($expr->var);
            $referencedClasses = \PHPStan\Type\TypeUtils::getDirectClassNames($methodCalledOnType);
            if (\count($referencedClasses) === 1 && $this->reflectionProvider->hasClass($referencedClasses[0])) {
                $methodClassReflection = $this->reflectionProvider->getClass($referencedClasses[0]);
                if ($methodClassReflection->hasMethod($expr->name->name)) {
                    $methodReflection = $methodClassReflection->getMethod($expr->name->name, $scope);
                    foreach ($this->getMethodTypeSpecifyingExtensionsForClass($methodClassReflection->getName()) as $extension) {
                        if (!$extension->isMethodSupported($methodReflection, $expr, $context)) {
                            continue;
                        }
                        return $extension->specifyTypes($methodReflection, $expr, $scope, $context);
                    }
                }
            }
            if ($this->rememberFunctionValues) {
                return $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\StaticCall && $expr->name instanceof \PhpParser\Node\Identifier) {
            if ($expr->class instanceof \PhpParser\Node\Name) {
                $calleeType = $scope->resolveTypeByName($expr->class);
            } else {
                $calleeType = $scope->getType($expr->class);
            }
            $staticMethodReflection = $scope->getMethodReflection($calleeType, $expr->name->name);
            if ($staticMethodReflection !== null) {
                $referencedClasses = \PHPStan\Type\TypeUtils::getDirectClassNames($calleeType);
                if (\count($referencedClasses) === 1 && $this->reflectionProvider->hasClass($referencedClasses[0])) {
                    $staticMethodClassReflection = $this->reflectionProvider->getClass($referencedClasses[0]);
                    foreach ($this->getStaticMethodTypeSpecifyingExtensionsForClass($staticMethodClassReflection->getName()) as $extension) {
                        if (!$extension->isStaticMethodSupported($staticMethodReflection, $expr, $context)) {
                            continue;
                        }
                        return $extension->specifyTypes($staticMethodReflection, $expr, $scope, $context);
                    }
                }
            }
            if ($this->rememberFunctionValues) {
                return $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
            }
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd || $expr instanceof \PhpParser\Node\Expr\BinaryOp\LogicalAnd) {
            $leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
            $rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
            $types = $context->true() ? $leftTypes->unionWith($rightTypes) : $leftTypes->intersectWith($rightTypes);
            if ($context->false()) {
                return new \PHPStan\Analyser\SpecifiedTypes($types->getSureTypes(), $types->getSureNotTypes(), \false, \array_merge($this->processBooleanConditionalTypes($scope, $leftTypes, $rightTypes), $this->processBooleanConditionalTypes($scope, $rightTypes, $leftTypes)));
            }
            return $types;
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr || $expr instanceof \PhpParser\Node\Expr\BinaryOp\LogicalOr) {
            $leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
            $rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
            $types = $context->true() ? $leftTypes->intersectWith($rightTypes) : $leftTypes->unionWith($rightTypes);
            if ($context->true()) {
                return new \PHPStan\Analyser\SpecifiedTypes($types->getSureTypes(), $types->getSureNotTypes(), \false, \array_merge($this->processBooleanConditionalTypes($scope, $leftTypes, $rightTypes), $this->processBooleanConditionalTypes($scope, $rightTypes, $leftTypes)));
            }
            return $types;
        } elseif ($expr instanceof \PhpParser\Node\Expr\BooleanNot && !$context->null()) {
            return $this->specifyTypesInCondition($scope, $expr->expr, $context->negate());
        } elseif ($expr instanceof \PhpParser\Node\Expr\Assign) {
            if (!$scope instanceof \PHPStan\Analyser\MutatingScope) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            if ($context->null()) {
                return $this->specifyTypesInCondition($scope->exitFirstLevelStatements(), $expr->expr, $context);
            }
            return $this->specifyTypesInCondition($scope->exitFirstLevelStatements(), $expr->var, $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\Isset_ && \count($expr->vars) > 0 && $context->true() || $expr instanceof \PhpParser\Node\Expr\Empty_ && $context->false()) {
            $vars = [];
            if ($expr instanceof \PhpParser\Node\Expr\Isset_) {
                $varsToIterate = $expr->vars;
            } else {
                $varsToIterate = [$expr->expr];
            }
            foreach ($varsToIterate as $var) {
                $tmpVars = [$var];
                while ($var instanceof \PhpParser\Node\Expr\ArrayDimFetch || $var instanceof \PhpParser\Node\Expr\PropertyFetch || $var instanceof \PhpParser\Node\Expr\StaticPropertyFetch && $var->class instanceof \PhpParser\Node\Expr) {
                    if ($var instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                        /** @var Expr $var */
                        $var = $var->class;
                    } else {
                        $var = $var->var;
                    }
                    $tmpVars[] = $var;
                }
                $vars = \array_merge($vars, \array_reverse($tmpVars));
            }
            if (\count($vars) === 0) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            $types = null;
            foreach ($vars as $var) {
                if ($var instanceof \PhpParser\Node\Expr\Variable && \is_string($var->name)) {
                    if ($scope->hasVariableType($var->name)->no()) {
                        return new \PHPStan\Analyser\SpecifiedTypes([], []);
                    }
                }
                if ($expr instanceof \PhpParser\Node\Expr\Isset_) {
                    if ($var instanceof \PhpParser\Node\Expr\ArrayDimFetch && $var->dim !== null && !$scope->getType($var->var) instanceof \PHPStan\Type\MixedType) {
                        $type = $this->create($var->var, new \PHPStan\Type\Accessory\HasOffsetType($scope->getType($var->dim)), $context, \false, $scope)->unionWith($this->create($var, new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope));
                    } else {
                        $type = $this->create($var, new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
                    }
                } else {
                    $type = $this->create($var, new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\Constant\ConstantBooleanType(\false)]), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
                }
                if ($var instanceof \PhpParser\Node\Expr\PropertyFetch && $var->name instanceof \PhpParser\Node\Identifier) {
                    $type = $type->unionWith($this->create($var->var, new \PHPStan\Type\IntersectionType([new \PHPStan\Type\ObjectWithoutClassType(), new \PHPStan\Type\Accessory\HasPropertyType($var->name->toString())]), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                } elseif ($var instanceof \PhpParser\Node\Expr\StaticPropertyFetch && $var->class instanceof \PhpParser\Node\Expr && $var->name instanceof \PhpParser\Node\VarLikeIdentifier) {
                    $type = $type->unionWith($this->create($var->class, new \PHPStan\Type\IntersectionType([new \PHPStan\Type\ObjectWithoutClassType(), new \PHPStan\Type\Accessory\HasPropertyType($var->name->toString())]), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope));
                }
                if ($types === null) {
                    $types = $type;
                } else {
                    $types = $types->unionWith($type);
                }
            }
            if ($expr instanceof \PhpParser\Node\Expr\Empty_ && (new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType()))->isSuperTypeOf($scope->getType($expr->expr))->yes()) {
                $types = $types->unionWith($this->create($expr->expr, new \PHPStan\Type\Accessory\NonEmptyArrayType(), $context->negate(), \false, $scope));
            }
            return $types;
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce && $context->true() && (new \PHPStan\Type\Constant\ConstantBooleanType(\false))->isSuperTypeOf($scope->getType($expr->right))->yes()) {
            return $this->create($expr->left, new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
        } elseif ($expr instanceof \PhpParser\Node\Expr\Empty_ && $context->truthy() && (new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType()))->isSuperTypeOf($scope->getType($expr->expr))->yes()) {
            return $this->create($expr->expr, new \PHPStan\Type\Accessory\NonEmptyArrayType(), $context->negate(), \false, $scope);
        } elseif ($expr instanceof \PhpParser\Node\Expr\ErrorSuppress) {
            return $this->specifyTypesInCondition($scope, $expr->expr, $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\Ternary && !$context->null() && (new \PHPStan\Type\Constant\ConstantBooleanType(\false))->isSuperTypeOf($scope->getType($expr->else))->yes()) {
            $conditionExpr = $expr->cond;
            if ($expr->if !== null) {
                $conditionExpr = new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($conditionExpr, $expr->if);
            }
            return $this->specifyTypesInCondition($scope, $conditionExpr, $context);
        } elseif ($expr instanceof \PhpParser\Node\Expr\NullsafePropertyFetch && !$context->null()) {
            $types = $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\BooleanAnd(new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr->var, new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'))), new \PhpParser\Node\Expr\PropertyFetch($expr->var, $expr->name)), $context);
            $nullSafeTypes = $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
            return $context->true() ? $types->unionWith($nullSafeTypes) : $types->intersectWith($nullSafeTypes);
        } elseif ($expr instanceof \PhpParser\Node\Expr\NullsafeMethodCall && !$context->null()) {
            $types = $this->specifyTypesInCondition($scope, new \PhpParser\Node\Expr\BinaryOp\BooleanAnd(new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr->var, new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'))), new \PhpParser\Node\Expr\MethodCall($expr->var, $expr->name, $expr->args)), $context);
            $nullSafeTypes = $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
            return $context->true() ? $types->unionWith($nullSafeTypes) : $types->intersectWith($nullSafeTypes);
        } elseif (!$context->null()) {
            return $this->handleDefaultTruthyOrFalseyContext($context, $expr, $scope);
        }
        return new \PHPStan\Analyser\SpecifiedTypes();
    }
    private function handleDefaultTruthyOrFalseyContext(\PHPStan\Analyser\TypeSpecifierContext $context, \PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope) : \PHPStan\Analyser\SpecifiedTypes
    {
        if ($context->null()) {
            return new \PHPStan\Analyser\SpecifiedTypes();
        }
        if (!$context->truthy()) {
            $type = \PHPStan\Type\StaticTypeFactory::truthy();
            return $this->create($expr, $type, \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
        } elseif (!$context->falsey()) {
            $type = \PHPStan\Type\StaticTypeFactory::falsey();
            return $this->create($expr, $type, \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
        }
        return new \PHPStan\Analyser\SpecifiedTypes();
    }
    /**
     * @param Scope $scope
     * @param SpecifiedTypes $leftTypes
     * @param SpecifiedTypes $rightTypes
     * @return array<string, ConditionalExpressionHolder[]>
     */
    private function processBooleanConditionalTypes(\PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\SpecifiedTypes $leftTypes, \PHPStan\Analyser\SpecifiedTypes $rightTypes) : array
    {
        $conditionExpressionTypes = [];
        foreach ($leftTypes->getSureNotTypes() as $exprString => list($expr, $type)) {
            if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!\is_string($expr->name)) {
                continue;
            }
            $conditionExpressionTypes[$exprString] = \PHPStan\Type\TypeCombinator::intersect($scope->getType($expr), $type);
        }
        if (\count($conditionExpressionTypes) > 0) {
            $holders = [];
            foreach ($rightTypes->getSureNotTypes() as $exprString => list($expr, $type)) {
                if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
                    continue;
                }
                if (!\is_string($expr->name)) {
                    continue;
                }
                if (!isset($holders[$exprString])) {
                    $holders[$exprString] = [];
                }
                $holders[$exprString][] = new \PHPStan\Analyser\ConditionalExpressionHolder($conditionExpressionTypes, new \PHPStan\Analyser\VariableTypeHolder(\PHPStan\Type\TypeCombinator::remove($scope->getType($expr), $type), \PHPStan\TrinaryLogic::createYes()));
            }
            return $holders;
        }
        return [];
    }
    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PhpParser\Node\Expr\BinaryOp $binaryOperation
     * @return mixed[]|null
     */
    private function findTypeExpressionsFromBinaryOperation(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr\BinaryOp $binaryOperation)
    {
        $leftType = $scope->getType($binaryOperation->left);
        $rightType = $scope->getType($binaryOperation->right);
        if ($leftType instanceof \PHPStan\Type\ConstantScalarType && !$binaryOperation->right instanceof \PhpParser\Node\Expr\ConstFetch && !$binaryOperation->right instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return [$binaryOperation->right, $leftType];
        } elseif ($rightType instanceof \PHPStan\Type\ConstantScalarType && !$binaryOperation->left instanceof \PhpParser\Node\Expr\ConstFetch && !$binaryOperation->left instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return [$binaryOperation->left, $rightType];
        }
        return null;
    }
    /** @api
     * @param \PHPStan\Analyser\Scope|null $scope */
    public function create(\PhpParser\Node\Expr $expr, \PHPStan\Type\Type $type, \PHPStan\Analyser\TypeSpecifierContext $context, bool $overwrite = \false, $scope = null) : \PHPStan\Analyser\SpecifiedTypes
    {
        if ($expr instanceof \PhpParser\Node\Expr\New_ || $expr instanceof \PhpParser\Node\Expr\Instanceof_) {
            return new \PHPStan\Analyser\SpecifiedTypes();
        }
        while ($expr instanceof \PhpParser\Node\Expr\Assign) {
            $expr = $expr->var;
        }
        if ($scope !== null) {
            if ($context->true()) {
                $resultType = \PHPStan\Type\TypeCombinator::intersect($scope->getType($expr), $type);
            } elseif ($context->false()) {
                $resultType = \PHPStan\Type\TypeCombinator::remove($scope->getType($expr), $type);
            }
        }
        $originalExpr = $expr;
        if (isset($resultType) && !\PHPStan\Type\TypeCombinator::containsNull($resultType)) {
            $expr = \PHPStan\Analyser\NullsafeOperatorHelper::getNullsafeShortcircuitedExpr($expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\FuncCall && $expr->name instanceof \PhpParser\Node\Name) {
            $has = $this->reflectionProvider->hasFunction($expr->name, $scope);
            if (!$has) {
                // backwards compatibility with previous behaviour
                return new \PHPStan\Analyser\SpecifiedTypes();
            }
            $functionReflection = $this->reflectionProvider->getFunction($expr->name, $scope);
            if ($functionReflection->hasSideEffects()->yes()) {
                return new \PHPStan\Analyser\SpecifiedTypes();
            }
        }
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall && $expr->name instanceof \PhpParser\Node\Identifier && $scope !== null) {
            $methodName = $expr->name->toString();
            $calledOnType = $scope->getType($expr->var);
            $methodReflection = $scope->getMethodReflection($calledOnType, $methodName);
            if ($methodReflection === null || $methodReflection->hasSideEffects()->yes()) {
                if (isset($resultType) && !\PHPStan\Type\TypeCombinator::containsNull($resultType)) {
                    return $this->createNullsafeTypes($originalExpr, $scope, $context, $type);
                }
                return new \PHPStan\Analyser\SpecifiedTypes();
            }
        }
        $sureTypes = [];
        $sureNotTypes = [];
        $exprString = $this->printer->prettyPrintExpr($expr);
        if ($context->false()) {
            $sureNotTypes[$exprString] = [$expr, $type];
        } elseif ($context->true()) {
            $sureTypes[$exprString] = [$expr, $type];
        }
        $types = new \PHPStan\Analyser\SpecifiedTypes($sureTypes, $sureNotTypes, $overwrite);
        if ($scope !== null && isset($resultType) && !\PHPStan\Type\TypeCombinator::containsNull($resultType)) {
            return $this->createNullsafeTypes($originalExpr, $scope, $context, $type)->unionWith($types);
        }
        return $types;
    }
    /**
     * @param \PHPStan\Type\Type|null $type
     */
    private function createNullsafeTypes(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\TypeSpecifierContext $context, $type) : \PHPStan\Analyser\SpecifiedTypes
    {
        if ($expr instanceof \PhpParser\Node\Expr\NullsafePropertyFetch) {
            if ($type !== null) {
                $propertyFetchTypes = $this->create(new \PhpParser\Node\Expr\PropertyFetch($expr->var, $expr->name), $type, $context, \false, $scope);
            } else {
                $propertyFetchTypes = $this->create(new \PhpParser\Node\Expr\PropertyFetch($expr->var, $expr->name), new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
            }
            return $propertyFetchTypes->unionWith($this->create($expr->var, new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope));
        }
        if ($expr instanceof \PhpParser\Node\Expr\NullsafeMethodCall) {
            if ($type !== null) {
                $methodCallTypes = $this->create(new \PhpParser\Node\Expr\MethodCall($expr->var, $expr->name, $expr->args), $type, $context, \false, $scope);
            } else {
                $methodCallTypes = $this->create(new \PhpParser\Node\Expr\MethodCall($expr->var, $expr->name, $expr->args), new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope);
            }
            return $methodCallTypes->unionWith($this->create($expr->var, new \PHPStan\Type\NullType(), \PHPStan\Analyser\TypeSpecifierContext::createFalse(), \false, $scope));
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return $this->createNullsafeTypes($expr->var, $scope, $context, null);
        }
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->createNullsafeTypes($expr->var, $scope, $context, null);
        }
        if ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return $this->createNullsafeTypes($expr->var, $scope, $context, null);
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch && $expr->class instanceof \PhpParser\Node\Expr) {
            return $this->createNullsafeTypes($expr->class, $scope, $context, null);
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticCall && $expr->class instanceof \PhpParser\Node\Expr) {
            return $this->createNullsafeTypes($expr->class, $scope, $context, null);
        }
        return new \PHPStan\Analyser\SpecifiedTypes();
    }
    private function createRangeTypes(\PhpParser\Node\Expr $expr, \PHPStan\Type\Type $type, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        $sureNotTypes = [];
        if ($type instanceof \PHPStan\Type\IntegerRangeType || $type instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            $exprString = $this->printer->prettyPrintExpr($expr);
            if ($context->false()) {
                $sureNotTypes[$exprString] = [$expr, $type];
            } elseif ($context->true()) {
                $inverted = \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\IntegerType(), $type);
                $sureNotTypes[$exprString] = [$expr, $inverted];
            }
        }
        return new \PHPStan\Analyser\SpecifiedTypes([], $sureNotTypes);
    }
    /**
     * @return \PHPStan\Type\FunctionTypeSpecifyingExtension[]
     */
    private function getFunctionTypeSpecifyingExtensions() : array
    {
        return $this->functionTypeSpecifyingExtensions;
    }
    /**
     * @param string $className
     * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
     */
    private function getMethodTypeSpecifyingExtensionsForClass(string $className) : array
    {
        if ($this->methodTypeSpecifyingExtensionsByClass === null) {
            $byClass = [];
            foreach ($this->methodTypeSpecifyingExtensions as $extension) {
                $byClass[$extension->getClass()][] = $extension;
            }
            $this->methodTypeSpecifyingExtensionsByClass = $byClass;
        }
        return $this->getTypeSpecifyingExtensionsForType($this->methodTypeSpecifyingExtensionsByClass, $className);
    }
    /**
     * @param string $className
     * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
     */
    private function getStaticMethodTypeSpecifyingExtensionsForClass(string $className) : array
    {
        if ($this->staticMethodTypeSpecifyingExtensionsByClass === null) {
            $byClass = [];
            foreach ($this->staticMethodTypeSpecifyingExtensions as $extension) {
                $byClass[$extension->getClass()][] = $extension;
            }
            $this->staticMethodTypeSpecifyingExtensionsByClass = $byClass;
        }
        return $this->getTypeSpecifyingExtensionsForType($this->staticMethodTypeSpecifyingExtensionsByClass, $className);
    }
    /**
     * @param \PHPStan\Type\MethodTypeSpecifyingExtension[][]|\PHPStan\Type\StaticMethodTypeSpecifyingExtension[][] $extensions
     * @param string $className
     * @return mixed[]
     */
    private function getTypeSpecifyingExtensionsForType(array $extensions, string $className) : array
    {
        $extensionsForClass = [[]];
        $class = $this->reflectionProvider->getClass($className);
        foreach (\array_merge([$className], $class->getParentClassesNames(), $class->getNativeReflection()->getInterfaceNames()) as $extensionClassName) {
            if (!isset($extensions[$extensionClassName])) {
                continue;
            }
            $extensionsForClass[] = $extensions[$extensionClassName];
        }
        return \array_merge(...$extensionsForClass);
    }
}
