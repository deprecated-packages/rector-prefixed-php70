<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector\SimplifyBoolIdenticalTrueRectorTest
 */
final class SimplifyBoolIdenticalTrueRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Symplify bool value compare to true or false', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;
         $match = in_array($value, $items, TRUE) !== FALSE;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);
         $match = in_array($value, $items, TRUE);
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
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if ($this->nodeTypeResolver->isStaticType($node->left, \PHPStan\Type\BooleanType::class) && !$this->valueResolver->isTrueOrFalse($node->left)) {
            return $this->processBoolTypeToNotBool($node, $node->left, $node->right);
        }
        if (!$this->nodeTypeResolver->isStaticType($node->right, \PHPStan\Type\BooleanType::class)) {
            return null;
        }
        if ($this->valueResolver->isTrueOrFalse($node->right)) {
            return null;
        }
        return $this->processBoolTypeToNotBool($node, $node->right, $node->left);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function processBoolTypeToNotBool(\PhpParser\Node $node, \PhpParser\Node\Expr $leftExpr, \PhpParser\Node\Expr $rightExpr)
    {
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return $this->refactorIdentical($leftExpr, $rightExpr);
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return $this->refactorNotIdentical($leftExpr, $rightExpr);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function refactorIdentical(\PhpParser\Node\Expr $leftExpr, \PhpParser\Node\Expr $rightExpr)
    {
        if ($this->valueResolver->isTrue($rightExpr)) {
            return $leftExpr;
        }
        if ($this->valueResolver->isFalse($rightExpr)) {
            // prevent !!
            if ($leftExpr instanceof \PhpParser\Node\Expr\BooleanNot) {
                return $leftExpr->expr;
            }
            return new \PhpParser\Node\Expr\BooleanNot($leftExpr);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function refactorNotIdentical(\PhpParser\Node\Expr $leftExpr, \PhpParser\Node\Expr $rightExpr)
    {
        if ($this->valueResolver->isFalse($rightExpr)) {
            return $leftExpr;
        }
        if ($this->valueResolver->isTrue($rightExpr)) {
            return new \PhpParser\Node\Expr\BooleanNot($leftExpr);
        }
        return null;
    }
}
