<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Class_>
 */
class ApiClassExtendsRule implements \PHPStan\Rules\Rule
{
    /** @var ApiRuleHelper */
    private $apiRuleHelper;
    /** @var ReflectionProvider */
    private $reflectionProvider;
    public function __construct(\PHPStan\Rules\Api\ApiRuleHelper $apiRuleHelper, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->apiRuleHelper = $apiRuleHelper;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt\Class_::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if ($node->extends === null) {
            return [];
        }
        $extendedClassName = (string) $node->extends;
        if (!$this->reflectionProvider->hasClass($extendedClassName)) {
            return [];
        }
        $extendedClassReflection = $this->reflectionProvider->getClass($extendedClassName);
        if (!$this->apiRuleHelper->isPhpStanCode($scope, $extendedClassReflection->getName(), $extendedClassReflection->getFileName() ?: null)) {
            return [];
        }
        if ($extendedClassReflection->getName() === \PHPStan\Analyser\MutatingScope::class) {
            return [];
        }
        $ruleError = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Extending %s is not covered by backward compatibility promise. The class might change in a minor PHPStan version.', $extendedClassReflection->getDisplayName()))->tip(\sprintf("If you think it should be covered by backward compatibility promise, open a discussion:\n   %s\n\n   See also:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise", 'https://github.com/phpstan/phpstan/discussions'))->build();
        $docBlock = $extendedClassReflection->getResolvedPhpDoc();
        if ($docBlock === null) {
            return [$ruleError];
        }
        foreach ($docBlock->getPhpDocNodes() as $phpDocNode) {
            $apiTags = $phpDocNode->getTagsByName('@api');
            if (\count($apiTags) > 0) {
                return [];
            }
        }
        return [$ruleError];
    }
}
