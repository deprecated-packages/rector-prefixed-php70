<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\Type\Type;
/**
 * @internal
 */
class DirectScopeFactory implements \PHPStan\Analyser\ScopeFactory
{
    /** @var string */
    private $scopeClass;
    /** @var \PHPStan\Reflection\ReflectionProvider */
    private $reflectionProvider;
    /** @var \PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider */
    private $dynamicReturnTypeExtensionRegistryProvider;
    /** @var OperatorTypeSpecifyingExtensionRegistryProvider */
    private $operatorTypeSpecifyingExtensionRegistryProvider;
    /** @var \PhpParser\PrettyPrinter\Standard */
    private $printer;
    /** @var \PHPStan\Analyser\TypeSpecifier */
    private $typeSpecifier;
    /** @var \PHPStan\Rules\Properties\PropertyReflectionFinder */
    private $propertyReflectionFinder;
    /** @var \PHPStan\Parser\Parser */
    private $parser;
    /** @var NodeScopeResolver */
    private $nodeScopeResolver;
    /** @var bool */
    private $treatPhpDocTypesAsCertain;
    /** @var bool */
    private $objectFromNewClass;
    /** @var string[] */
    private $dynamicConstantNames;
    public function __construct(string $scopeClass, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider $dynamicReturnTypeExtensionRegistryProvider, \PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider $operatorTypeSpecifyingExtensionRegistryProvider, \PhpParser\PrettyPrinter\Standard $printer, \PHPStan\Analyser\TypeSpecifier $typeSpecifier, \PHPStan\Rules\Properties\PropertyReflectionFinder $propertyReflectionFinder, \PHPStan\Parser\Parser $parser, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, bool $treatPhpDocTypesAsCertain, bool $objectFromNewClass, \PHPStan\DependencyInjection\Container $container)
    {
        $this->scopeClass = $scopeClass;
        $this->reflectionProvider = $reflectionProvider;
        $this->dynamicReturnTypeExtensionRegistryProvider = $dynamicReturnTypeExtensionRegistryProvider;
        $this->operatorTypeSpecifyingExtensionRegistryProvider = $operatorTypeSpecifyingExtensionRegistryProvider;
        $this->printer = $printer;
        $this->typeSpecifier = $typeSpecifier;
        $this->propertyReflectionFinder = $propertyReflectionFinder;
        $this->parser = $parser;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
        $this->objectFromNewClass = $objectFromNewClass;
        $this->dynamicConstantNames = $container->getParameter('dynamicConstantNames');
    }
    /**
     * @param \PHPStan\Analyser\ScopeContext $context
     * @param bool $declareStrictTypes
     * @param  array<string, Type> $constantTypes
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null $function
     * @param string|null $namespace
     * @param \PHPStan\Analyser\VariableTypeHolder[] $variablesTypes
     * @param \PHPStan\Analyser\VariableTypeHolder[] $moreSpecificTypes
     * @param array<string, ConditionalExpressionHolder[]> $conditionalExpressions
     * @param string|null $inClosureBindScopeClass
     * @param \PHPStan\Reflection\ParametersAcceptor|null $anonymousFunctionReflection
     * @param bool $inFirstLevelStatement
     * @param array<string, true> $currentlyAssignedExpressions
     * @param array<string, Type> $nativeExpressionTypes
     * @param array<\PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection> $inFunctionCallsStack
     * @param bool $afterExtractCall
     * @param Scope|null $parentScope
     *
     * @return MutatingScope
     */
    public function create(\PHPStan\Analyser\ScopeContext $context, bool $declareStrictTypes = \false, array $constantTypes = [], $function = null, $namespace = null, array $variablesTypes = [], array $moreSpecificTypes = [], array $conditionalExpressions = [], $inClosureBindScopeClass = null, $anonymousFunctionReflection = null, bool $inFirstLevelStatement = \true, array $currentlyAssignedExpressions = [], array $nativeExpressionTypes = [], array $inFunctionCallsStack = [], bool $afterExtractCall = \false, $parentScope = null) : \PHPStan\Analyser\MutatingScope
    {
        $scopeClass = $this->scopeClass;
        if (!\is_a($scopeClass, \PHPStan\Analyser\MutatingScope::class, \true)) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return new $scopeClass($this, $this->reflectionProvider, $this->dynamicReturnTypeExtensionRegistryProvider->getRegistry(), $this->operatorTypeSpecifyingExtensionRegistryProvider->getRegistry(), $this->printer, $this->typeSpecifier, $this->propertyReflectionFinder, $this->parser, $this->nodeScopeResolver, $context, $declareStrictTypes, $constantTypes, $function, $namespace, $variablesTypes, $moreSpecificTypes, $conditionalExpressions, $inClosureBindScopeClass, $anonymousFunctionReflection, $inFirstLevelStatement, $currentlyAssignedExpressions, $nativeExpressionTypes, $inFunctionCallsStack, $this->dynamicConstantNames, $this->treatPhpDocTypesAsCertain, $this->objectFromNewClass, $afterExtractCall, $parentScope);
    }
}
