<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FinallyExitPointsNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<FinallyExitPointsNode>
 */
class OverwrittenExitPointByFinallyRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PHPStan\Node\FinallyExitPointsNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (\count($node->getTryCatchExitPoints()) === 0) {
            return [];
        }
        $errors = [];
        foreach ($node->getTryCatchExitPoints() as $exitPoint) {
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('This %s is overwritten by a different one in the finally block below.', $this->describeExitPoint($exitPoint->getStatement())))->line($exitPoint->getStatement()->getLine())->build();
        }
        foreach ($node->getFinallyExitPoints() as $exitPoint) {
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('The overwriting %s is on this line.', $this->describeExitPoint($exitPoint->getStatement())))->line($exitPoint->getStatement()->getLine())->build();
        }
        return $errors;
    }
    private function describeExitPoint(\PhpParser\Node\Stmt $stmt) : string
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\Return_) {
            return 'return';
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\Throw_) {
            return 'throw';
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\Continue_) {
            return 'continue';
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\Break_) {
            return 'break';
        }
        return 'exit point';
    }
}
