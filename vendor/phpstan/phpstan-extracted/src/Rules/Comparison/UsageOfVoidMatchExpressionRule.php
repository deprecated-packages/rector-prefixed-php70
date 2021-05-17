<?php

declare (strict_types=1);
namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VoidType;
/**
 * @implements Rule<Node\Expr\Match_>
 */
class UsageOfVoidMatchExpressionRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PhpParser\Node\Expr\Match_::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $matchResultType = $scope->getType($node);
        if ($matchResultType instanceof \PHPStan\Type\VoidType && !$scope->isInFirstLevelStatement()) {
            return [\PHPStan\Rules\RuleErrorBuilder::message('Result of match expression (void) is used.')->build()];
        }
        return [];
    }
}
