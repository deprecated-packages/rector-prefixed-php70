<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Node\Expr\New_>
 */
class ApiInstantiationRule implements \PHPStan\Rules\Rule
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
        return \PhpParser\Node\Expr\New_::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node->class instanceof \PhpParser\Node\Name) {
            return [];
        }
        $className = $scope->resolveName($node->class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$this->apiRuleHelper->isPhpStanCode($scope, $classReflection->getName(), $classReflection->getFileName() ?: null)) {
            return [];
        }
        $ruleError = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Creating new %s is not covered by backward compatibility promise. The class might change in a minor PHPStan version.', $classReflection->getDisplayName()))->tip(\sprintf("If you think it should be covered by backward compatibility promise, open a discussion:\n   %s\n\n   See also:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise", 'https://github.com/phpstan/phpstan/discussions'))->build();
        if (!$classReflection->hasConstructor()) {
            return [$ruleError];
        }
        $constructor = $classReflection->getConstructor();
        $docComment = $constructor->getDocComment();
        if ($docComment === null) {
            return [$ruleError];
        }
        if (\strpos($docComment, '@api') === \false) {
            return [$ruleError];
        }
        if ($constructor->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return [$ruleError];
        }
        return [];
    }
}
