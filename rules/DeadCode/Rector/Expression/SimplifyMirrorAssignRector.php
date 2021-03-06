<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Expression\SimplifyMirrorAssignRector\SimplifyMirrorAssignRectorTest
 */
final class SimplifyMirrorAssignRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes unneeded $a = $a assigns', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$a = $a;', '')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param Expression $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        /** @var Assign $assignNode */
        $assignNode = $node->expr;
        if ($this->nodeComparator->areNodesEqual($assignNode->var, $assignNode->expr)) {
            $this->removeNode($node);
        }
        return null;
    }
}
