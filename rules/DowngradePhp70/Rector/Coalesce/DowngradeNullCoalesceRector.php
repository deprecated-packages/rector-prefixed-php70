<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Coalesce;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/isset_ternary
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\DowngradeNullCoalesceRectorTest
 */
final class DowngradeNullCoalesceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Coalesce::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change null coalesce to isset ternary check', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$username = $_GET['user'] ?? 'nobody';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$username = isset($_GET['user']) ? $_GET['user'] : 'nobody';
CODE_SAMPLE
)]);
    }
    /**
     * @param Coalesce $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $if = $node->left;
        $else = $node->right;
        if ($if instanceof \PhpParser\Node\Expr\Variable || $if instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $cond = new \PhpParser\Node\Expr\Isset_([$if]);
        } else {
            $cond = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($if, $this->nodeFactory->createNull());
        }
        return new \PhpParser\Node\Expr\Ternary($cond, $if, $else);
    }
}
