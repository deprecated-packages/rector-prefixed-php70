<?php

declare (strict_types=1);
namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;
/**
 * @implements \PHPStan\Rules\Rule<InClassMethodNode>
 */
class MethodSignatureRule implements \PHPStan\Rules\Rule
{
    /** @var bool */
    private $reportMaybes;
    /** @var bool */
    private $reportStatic;
    public function __construct(bool $reportMaybes, bool $reportStatic)
    {
        $this->reportMaybes = $reportMaybes;
        $this->reportStatic = $reportStatic;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\InClassMethodNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $method = $scope->getFunction();
        if (!$method instanceof \PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection) {
            return [];
        }
        $methodName = $method->getName();
        if ($methodName === '__construct') {
            return [];
        }
        if (!$this->reportStatic && $method->isStatic()) {
            return [];
        }
        if ($method->isPrivate()) {
            return [];
        }
        $parameters = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($method->getVariants());
        $errors = [];
        $declaringClass = $method->getDeclaringClass();
        foreach ($this->collectParentMethods($methodName, $method->getDeclaringClass()) as $parentMethod) {
            $parentVariants = $parentMethod->getVariants();
            if (\count($parentVariants) !== 1) {
                continue;
            }
            $parentParameters = $parentVariants[0];
            if (!$parentParameters instanceof \PHPStan\Reflection\ParametersAcceptorWithPhpDocs) {
                continue;
            }
            list($returnTypeCompatibility, $returnType, $parentReturnType) = $this->checkReturnTypeCompatibility($declaringClass, $parameters, $parentParameters);
            if ($returnTypeCompatibility->no() || !$returnTypeCompatibility->yes() && $this->reportMaybes) {
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Return type (%s) of method %s::%s() should be %s with return type (%s) of method %s::%s()', $returnType->describe(\PHPStan\Type\VerbosityLevel::value()), $method->getDeclaringClass()->getDisplayName(), $method->getName(), $returnTypeCompatibility->no() ? 'compatible' : 'covariant', $parentReturnType->describe(\PHPStan\Type\VerbosityLevel::value()), $parentMethod->getDeclaringClass()->getDisplayName(), $parentMethod->getName()))->build();
            }
            $parameterResults = $this->checkParameterTypeCompatibility($declaringClass, $parameters->getParameters(), $parentParameters->getParameters());
            foreach ($parameterResults as $parameterIndex => list($parameterResult, $parameterType, $parentParameterType)) {
                if ($parameterResult->yes()) {
                    continue;
                }
                if (!$parameterResult->no() && !$this->reportMaybes) {
                    continue;
                }
                $parameter = $parameters->getParameters()[$parameterIndex];
                $parentParameter = $parentParameters->getParameters()[$parameterIndex];
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Parameter #%d $%s (%s) of method %s::%s() should be %s with parameter $%s (%s) of method %s::%s()', $parameterIndex + 1, $parameter->getName(), $parameterType->describe(\PHPStan\Type\VerbosityLevel::value()), $method->getDeclaringClass()->getDisplayName(), $method->getName(), $parameterResult->no() ? 'compatible' : 'contravariant', $parentParameter->getName(), $parentParameterType->describe(\PHPStan\Type\VerbosityLevel::value()), $parentMethod->getDeclaringClass()->getDisplayName(), $parentMethod->getName()))->build();
            }
        }
        return $errors;
    }
    /**
     * @param string $methodName
     * @param \PHPStan\Reflection\ClassReflection $class
     * @return \PHPStan\Reflection\MethodReflection[]
     */
    private function collectParentMethods(string $methodName, \PHPStan\Reflection\ClassReflection $class) : array
    {
        $parentMethods = [];
        $parentClass = $class->getParentClass();
        if ($parentClass !== \false && $parentClass->hasNativeMethod($methodName)) {
            $parentMethod = $parentClass->getNativeMethod($methodName);
            if (!$parentMethod->isPrivate()) {
                $parentMethods[] = $parentMethod;
            }
        }
        foreach ($class->getInterfaces() as $interface) {
            if (!$interface->hasNativeMethod($methodName)) {
                continue;
            }
            $parentMethods[] = $interface->getNativeMethod($methodName);
        }
        return $parentMethods;
    }
    /**
     * @param ParametersAcceptorWithPhpDocs $currentVariant
     * @param ParametersAcceptorWithPhpDocs $parentVariant
     * @return array{TrinaryLogic, Type, Type}
     */
    private function checkReturnTypeCompatibility(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $currentVariant, \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parentVariant) : array
    {
        $returnType = \PHPStan\Type\TypehintHelper::decideType($currentVariant->getNativeReturnType(), \PHPStan\Type\Generic\TemplateTypeHelper::resolveToBounds($currentVariant->getPhpDocReturnType()));
        $originalParentReturnType = \PHPStan\Type\TypehintHelper::decideType($parentVariant->getNativeReturnType(), \PHPStan\Type\Generic\TemplateTypeHelper::resolveToBounds($parentVariant->getPhpDocReturnType()));
        $parentReturnType = $this->transformStaticType($declaringClass, $originalParentReturnType);
        // Allow adding `void` return type hints when the parent defines no return type
        if ($returnType instanceof \PHPStan\Type\VoidType && $parentReturnType instanceof \PHPStan\Type\MixedType) {
            return [\PHPStan\TrinaryLogic::createYes(), $returnType, $parentReturnType];
        }
        // We can return anything
        if ($parentReturnType instanceof \PHPStan\Type\VoidType) {
            return [\PHPStan\TrinaryLogic::createYes(), $returnType, $parentReturnType];
        }
        return [$parentReturnType->isSuperTypeOf($returnType), \PHPStan\Type\TypehintHelper::decideType($currentVariant->getNativeReturnType(), $currentVariant->getPhpDocReturnType()), $originalParentReturnType];
    }
    /**
     * @param \PHPStan\Reflection\ParameterReflectionWithPhpDocs[] $parameters
     * @param \PHPStan\Reflection\ParameterReflectionWithPhpDocs[] $parentParameters
     * @return array<int, array{TrinaryLogic, Type, Type}>
     */
    private function checkParameterTypeCompatibility(\PHPStan\Reflection\ClassReflection $declaringClass, array $parameters, array $parentParameters) : array
    {
        $parameterResults = [];
        $numberOfParameters = \min(\count($parameters), \count($parentParameters));
        for ($i = 0; $i < $numberOfParameters; $i++) {
            $parameter = $parameters[$i];
            $parentParameter = $parentParameters[$i];
            $parameterType = \PHPStan\Type\TypehintHelper::decideType($parameter->getNativeType(), \PHPStan\Type\Generic\TemplateTypeHelper::resolveToBounds($parameter->getPhpDocType()));
            $originalParameterType = \PHPStan\Type\TypehintHelper::decideType($parentParameter->getNativeType(), \PHPStan\Type\Generic\TemplateTypeHelper::resolveToBounds($parentParameter->getPhpDocType()));
            $parentParameterType = $this->transformStaticType($declaringClass, $originalParameterType);
            $parameterResults[] = [$parameterType->isSuperTypeOf($parentParameterType), \PHPStan\Type\TypehintHelper::decideType($parameter->getNativeType(), $parameter->getPhpDocType()), $originalParameterType];
        }
        return $parameterResults;
    }
    private function transformStaticType(\PHPStan\Reflection\ClassReflection $declaringClass, \PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) use($declaringClass) : Type {
            if ($type instanceof \PHPStan\Type\StaticType) {
                if ($declaringClass->isFinal()) {
                    $changedType = new \PHPStan\Type\ObjectType($declaringClass->getName());
                } else {
                    $changedType = $type->changeBaseClass($declaringClass);
                }
                return $traverse($changedType);
            }
            return $traverse($type);
        });
    }
}
