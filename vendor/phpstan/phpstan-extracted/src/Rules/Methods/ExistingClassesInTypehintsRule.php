<?php

declare (strict_types=1);
namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Rules\FunctionDefinitionCheck;
/**
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\InClassMethodNode>
 */
class ExistingClassesInTypehintsRule implements \PHPStan\Rules\Rule
{
    /** @var \PHPStan\Rules\FunctionDefinitionCheck */
    private $check;
    public function __construct(\PHPStan\Rules\FunctionDefinitionCheck $check)
    {
        $this->check = $check;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\InClassMethodNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $methodReflection = $scope->getFunction();
        if (!$methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return $this->check->checkClassMethod($methodReflection, $node->getOriginalNode(), \sprintf('Parameter $%%s of method %s::%s() has invalid typehint type %%s.', $scope->getClassReflection()->getDisplayName(), $methodReflection->getName()), \sprintf('Return typehint of method %s::%s() has invalid type %%s.', $scope->getClassReflection()->getDisplayName(), $methodReflection->getName()), \sprintf('Method %s::%s() uses native union types but they\'re supported only on PHP 8.0 and later.', $scope->getClassReflection()->getDisplayName(), $methodReflection->getName()), \sprintf('Template type %%s of method %s::%s() is not referenced in a parameter.', $scope->getClassReflection()->getDisplayName(), $methodReflection->getName()));
    }
}
