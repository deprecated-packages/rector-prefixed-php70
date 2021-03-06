<?php

declare (strict_types=1);
namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
class ImpossibleCheckTypeHelper
{
    /** @var \PHPStan\Reflection\ReflectionProvider */
    private $reflectionProvider;
    /** @var \PHPStan\Analyser\TypeSpecifier */
    private $typeSpecifier;
    /** @var string[] */
    private $universalObjectCratesClasses;
    /** @var bool */
    private $treatPhpDocTypesAsCertain;
    /**
     * @param \PHPStan\Reflection\ReflectionProvider $reflectionProvider
     * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
     * @param string[] $universalObjectCratesClasses
     * @param bool $treatPhpDocTypesAsCertain
     */
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \PHPStan\Analyser\TypeSpecifier $typeSpecifier, array $universalObjectCratesClasses, bool $treatPhpDocTypesAsCertain)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->typeSpecifier = $typeSpecifier;
        $this->universalObjectCratesClasses = $universalObjectCratesClasses;
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
    }
    /**
     * @return bool|null
     */
    public function findSpecifiedType(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall && \count($node->args) > 0) {
            if ($node->name instanceof \PhpParser\Node\Name) {
                $functionName = \strtolower((string) $node->name);
                if ($functionName === 'assert') {
                    $assertValue = $scope->getType($node->args[0]->value)->toBoolean();
                    if (!$assertValue instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                        return null;
                    }
                    return $assertValue->getValue();
                }
                if (\in_array($functionName, ['class_exists', 'interface_exists', 'trait_exists'], \true)) {
                    return null;
                }
                if ($functionName === 'count') {
                    return null;
                } elseif ($functionName === 'defined') {
                    return null;
                } elseif ($functionName === 'in_array' && \count($node->args) >= 3) {
                    $haystackType = $scope->getType($node->args[1]->value);
                    if ($haystackType instanceof \PHPStan\Type\MixedType) {
                        return null;
                    }
                    if (!$haystackType->isArray()->yes()) {
                        return null;
                    }
                    if (!$haystackType instanceof \PHPStan\Type\Constant\ConstantArrayType || \count($haystackType->getValueTypes()) > 0) {
                        $needleType = $scope->getType($node->args[0]->value);
                        $haystackArrayTypes = \PHPStan\Type\TypeUtils::getArrays($haystackType);
                        if (\count($haystackArrayTypes) === 1 && $haystackArrayTypes[0]->getIterableValueType() instanceof \PHPStan\Type\NeverType) {
                            return null;
                        }
                        $valueType = $haystackType->getIterableValueType();
                        $isNeedleSupertype = $needleType->isSuperTypeOf($valueType);
                        if ($isNeedleSupertype->maybe() || $isNeedleSupertype->yes()) {
                            foreach ($haystackArrayTypes as $haystackArrayType) {
                                foreach (\PHPStan\Type\TypeUtils::getConstantScalars($haystackArrayType->getIterableValueType()) as $constantScalarType) {
                                    if ($needleType->isSuperTypeOf($constantScalarType)->yes()) {
                                        continue 2;
                                    }
                                }
                                return null;
                            }
                        }
                        if ($isNeedleSupertype->yes()) {
                            $hasConstantNeedleTypes = \count(\PHPStan\Type\TypeUtils::getConstantScalars($needleType)) > 0;
                            $hasConstantHaystackTypes = \count(\PHPStan\Type\TypeUtils::getConstantScalars($valueType)) > 0;
                            if (!$hasConstantNeedleTypes && !$hasConstantHaystackTypes || $hasConstantNeedleTypes !== $hasConstantHaystackTypes) {
                                return null;
                            }
                        }
                    }
                } elseif ($functionName === 'method_exists' && \count($node->args) >= 2) {
                    $objectType = $scope->getType($node->args[0]->value);
                    $methodType = $scope->getType($node->args[1]->value);
                    if ($objectType instanceof \PHPStan\Type\Constant\ConstantStringType && !$this->reflectionProvider->hasClass($objectType->getValue())) {
                        return \false;
                    }
                    if ($methodType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                        if ($objectType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                            $objectType = new \PHPStan\Type\ObjectType($objectType->getValue());
                        }
                        if ($objectType instanceof \PHPStan\Type\TypeWithClassName) {
                            if ($objectType->hasMethod($methodType->getValue())->yes()) {
                                return \true;
                            }
                            if ($objectType->hasMethod($methodType->getValue())->no()) {
                                return \false;
                            }
                        }
                    }
                }
            }
        }
        $specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($scope, $node, \PHPStan\Analyser\TypeSpecifierContext::createTruthy());
        $sureTypes = $specifiedTypes->getSureTypes();
        $sureNotTypes = $specifiedTypes->getSureNotTypes();
        $isSpecified = static function (\PhpParser\Node\Expr $expr) use($scope, $node) : bool {
            if ($expr === $node) {
                return \true;
            }
            if ($expr instanceof \PhpParser\Node\Expr\Variable && \is_string($expr->name) && !$scope->hasVariableType($expr->name)->yes()) {
                return \true;
            }
            return ($node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall) && $scope->isSpecified($expr);
        };
        if (\count($sureTypes) === 1 && \count($sureNotTypes) === 0) {
            $sureType = \reset($sureTypes);
            if ($isSpecified($sureType[0])) {
                return null;
            }
            if ($this->treatPhpDocTypesAsCertain) {
                $argumentType = $scope->getType($sureType[0]);
            } else {
                $argumentType = $scope->getNativeType($sureType[0]);
            }
            /** @var \PHPStan\Type\Type $resultType */
            $resultType = $sureType[1];
            $isSuperType = $resultType->isSuperTypeOf($argumentType);
            if ($isSuperType->yes()) {
                return \true;
            } elseif ($isSuperType->no()) {
                return \false;
            }
            return null;
        } elseif (\count($sureNotTypes) === 1 && \count($sureTypes) === 0) {
            $sureNotType = \reset($sureNotTypes);
            if ($isSpecified($sureNotType[0])) {
                return null;
            }
            if ($this->treatPhpDocTypesAsCertain) {
                $argumentType = $scope->getType($sureNotType[0]);
            } else {
                $argumentType = $scope->getNativeType($sureNotType[0]);
            }
            /** @var \PHPStan\Type\Type $resultType */
            $resultType = $sureNotType[1];
            $isSuperType = $resultType->isSuperTypeOf($argumentType);
            if ($isSuperType->yes()) {
                return \false;
            } elseif ($isSuperType->no()) {
                return \true;
            }
            return null;
        }
        if (\count($sureTypes) > 0) {
            foreach ($sureTypes as $sureType) {
                if ($isSpecified($sureType[0])) {
                    return null;
                }
            }
            $types = \PHPStan\Type\TypeCombinator::union(...\array_column($sureTypes, 1));
            if ($types instanceof \PHPStan\Type\NeverType) {
                return \false;
            }
        }
        if (\count($sureNotTypes) > 0) {
            foreach ($sureNotTypes as $sureNotType) {
                if ($isSpecified($sureNotType[0])) {
                    return null;
                }
            }
            $types = \PHPStan\Type\TypeCombinator::union(...\array_column($sureNotTypes, 1));
            if ($types instanceof \PHPStan\Type\NeverType) {
                return \true;
            }
        }
        return null;
    }
    /**
     * @param Scope $scope
     * @param \PhpParser\Node\Arg[] $args
     * @return string
     */
    public function getArgumentsDescription(\PHPStan\Analyser\Scope $scope, array $args) : string
    {
        if (\count($args) === 0) {
            return '';
        }
        $descriptions = \array_map(static function (\PhpParser\Node\Arg $arg) use($scope) : string {
            return $scope->getType($arg->value)->describe(\PHPStan\Type\VerbosityLevel::value());
        }, $args);
        if (\count($descriptions) < 3) {
            return \sprintf(' with %s', \implode(' and ', $descriptions));
        }
        $lastDescription = \array_pop($descriptions);
        return \sprintf(' with arguments %s and %s', \implode(', ', $descriptions), $lastDescription);
    }
    /**
     * @return $this
     */
    public function doNotTreatPhpDocTypesAsCertain()
    {
        if (!$this->treatPhpDocTypesAsCertain) {
            return $this;
        }
        return new self($this->reflectionProvider, $this->typeSpecifier, $this->universalObjectCratesClasses, \false);
    }
}
