<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
/**
 * @implements Rule<Class_>
 */
class ApiClassImplementsRule implements \PHPStan\Rules\Rule
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
        $errors = [];
        foreach ($node->implements as $implements) {
            $errors = \array_merge($errors, $this->checkName($scope, $implements));
        }
        return $errors;
    }
    /**
     * @param Node\Name $name
     * @return RuleError[]
     */
    private function checkName(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Name $name) : array
    {
        $implementedClassName = (string) $name;
        if (!$this->reflectionProvider->hasClass($implementedClassName)) {
            return [];
        }
        $implementedClassReflection = $this->reflectionProvider->getClass($implementedClassName);
        if (!$this->apiRuleHelper->isPhpStanCode($scope, $implementedClassReflection->getName(), $implementedClassReflection->getFileName() ?: null)) {
            return [];
        }
        $ruleError = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Implementing %s is not covered by backward compatibility promise. The interface might change in a minor PHPStan version.', $implementedClassReflection->getDisplayName()))->tip(\sprintf("If you think it should be covered by backward compatibility promise, open a discussion:\n   %s\n\n   See also:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise", 'https://github.com/phpstan/phpstan/discussions'))->build();
        if ($implementedClassReflection->getName() === \PHPStan\Type\Type::class) {
            return [$ruleError];
        }
        $docBlock = $implementedClassReflection->getResolvedPhpDoc();
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
