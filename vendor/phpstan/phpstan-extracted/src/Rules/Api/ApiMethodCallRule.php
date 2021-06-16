<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Node\Expr\MethodCall>
 */
class ApiMethodCallRule implements \PHPStan\Rules\Rule
{
    /** @var ApiRuleHelper */
    private $apiRuleHelper;
    public function __construct(\PHPStan\Rules\Api\ApiRuleHelper $apiRuleHelper)
    {
        $this->apiRuleHelper = $apiRuleHelper;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Expr\MethodCall::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node->name instanceof \PhpParser\Node\Identifier) {
            return [];
        }
        $methodReflection = $scope->getMethodReflection($scope->getType($node->var), $node->name->toString());
        if ($methodReflection === null) {
            return [];
        }
        $declaringClass = $methodReflection->getDeclaringClass();
        if (!$this->apiRuleHelper->isPhpStanCode($scope, $declaringClass->getName(), $declaringClass->getFileName() ?: null)) {
            return [];
        }
        if ($this->isCovered($methodReflection)) {
            return [];
        }
        $ruleError = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Calling %s::%s() is not covered by backward compatibility promise. The method might change in a minor PHPStan version.', $declaringClass->getDisplayName(), $methodReflection->getName()))->tip(\sprintf("If you think it should be covered by backward compatibility promise, open a discussion:\n   %s\n\n   See also:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise", 'https://github.com/phpstan/phpstan/discussions'))->build();
        return [$ruleError];
    }
    private function isCovered(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        $declaringClass = $methodReflection->getDeclaringClass();
        $classDocBlock = $declaringClass->getResolvedPhpDoc();
        if ($classDocBlock !== null) {
            foreach ($classDocBlock->getPhpDocNodes() as $phpDocNode) {
                $apiTags = $phpDocNode->getTagsByName('@api');
                if (\count($apiTags) > 0) {
                    return \true;
                }
            }
        }
        $methodDocComment = $methodReflection->getDocComment();
        if ($methodDocComment === null) {
            return \false;
        }
        return \strpos($methodDocComment, '@api') !== \false;
    }
}
