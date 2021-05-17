<?php

declare (strict_types=1);
namespace PHPStan\Rules\Keywords;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Stmt>
 */
class ContinueBreakInLoopRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Continue_ && !$node instanceof \PhpParser\Node\Stmt\Break_) {
            return [];
        }
        if (!$node->num instanceof \PhpParser\Node\Scalar\LNumber) {
            $value = 1;
        } else {
            $value = $node->num->value;
        }
        $parent = $node->getAttribute('parent');
        while ($value > 0) {
            if ($parent === null || $parent instanceof \PhpParser\Node\Stmt\Function_ || $parent instanceof \PhpParser\Node\Stmt\ClassMethod || $parent instanceof \PhpParser\Node\Expr\Closure) {
                return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Keyword %s used outside of a loop or a switch statement.', $node instanceof \PhpParser\Node\Stmt\Continue_ ? 'continue' : 'break'))->nonIgnorable()->build()];
            }
            if ($parent instanceof \PhpParser\Node\Stmt\For_ || $parent instanceof \PhpParser\Node\Stmt\Foreach_ || $parent instanceof \PhpParser\Node\Stmt\Do_ || $parent instanceof \PhpParser\Node\Stmt\While_) {
                $value--;
            }
            if ($parent instanceof \PhpParser\Node\Stmt\Case_) {
                $value--;
                $parent = $parent->getAttribute('parent');
                if (!$parent instanceof \PhpParser\Node\Stmt\Switch_) {
                    throw new \PHPStan\ShouldNotHappenException();
                }
            }
            $parent = $parent->getAttribute('parent');
        }
        return [];
    }
}
