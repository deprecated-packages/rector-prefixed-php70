<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CatchWithUnthrownExceptionNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
/**
 * @implements Rule<CatchWithUnthrownExceptionNode>
 */
class CatchWithUnthrownExceptionRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PHPStan\Node\CatchWithUnthrownExceptionNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Dead catch - %s is never thrown in the try block.', $node->getCaughtType()->describe(\PHPStan\Type\VerbosityLevel::typeOnly())))->line($node->getLine())->build()];
    }
}
