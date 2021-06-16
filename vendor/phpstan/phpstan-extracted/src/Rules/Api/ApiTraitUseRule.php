<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Node\Stmt\TraitUse>
 */
class ApiTraitUseRule implements \PHPStan\Rules\Rule
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
        return \PhpParser\Node\Stmt\TraitUse::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $errors = [];
        $tip = \sprintf("If you think it should be covered by backward compatibility promise, open a discussion:\n   %s\n\n   See also:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise", 'https://github.com/phpstan/phpstan/discussions');
        foreach ($node->traits as $traitName) {
            $traitName = $traitName->toString();
            if (!$this->reflectionProvider->hasClass($traitName)) {
                continue;
            }
            $traitReflection = $this->reflectionProvider->getClass($traitName);
            if (!$this->apiRuleHelper->isPhpStanCode($scope, $traitReflection->getName(), $traitReflection->getFileName() ?: null)) {
                continue;
            }
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Using %s is not covered by backward compatibility promise. The trait might change in a minor PHPStan version.', $traitReflection->getDisplayName()))->tip($tip)->build();
        }
        return $errors;
    }
}
