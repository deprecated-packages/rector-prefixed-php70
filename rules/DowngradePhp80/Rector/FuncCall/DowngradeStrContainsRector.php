<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/str_contains
 *
 * @see Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector\DowngradeStrContainsRectorTest
 */
final class DowngradeStrContainsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace str_contains() with strpos() !== false', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     * @return Identical|NotIdentical|null The refactored node.
     */
    public function refactor(\PhpParser\Node $node)
    {
        $funcCall = $this->matchStrContainsOrNotStrContains($node);
        if (!$funcCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $haystack = $funcCall->args[0]->value;
        $needle = $funcCall->args[1]->value;
        $funcCall = $this->nodeFactory->createFuncCall('strpos', [$haystack, $needle]);
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($funcCall, $this->nodeFactory->createFalse());
        }
        return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($funcCall, $this->nodeFactory->createFalse());
    }
    /**
     * @param FuncCall|BooleanNot $expr
     * @return \PhpParser\Node\Expr\FuncCall|null
     */
    private function matchStrContainsOrNotStrContains(\PhpParser\Node\Expr $expr)
    {
        $expr = $expr instanceof \PhpParser\Node\Expr\BooleanNot ? $expr->expr : $expr;
        if (!$expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($expr, 'str_contains')) {
            return null;
        }
        return $expr;
    }
}
