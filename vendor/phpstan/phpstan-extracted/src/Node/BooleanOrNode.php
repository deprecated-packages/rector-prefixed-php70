<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
class BooleanOrNode extends \PhpParser\NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /** @var BooleanOr|LogicalOr */
    private $originalNode;
    /** @var Scope */
    private $rightScope;
    /**
     * @param BooleanOr|LogicalOr $originalNode
     * @param Scope $rightScope
     */
    public function __construct($originalNode, \PHPStan\Analyser\Scope $rightScope)
    {
        parent::__construct($originalNode->getAttributes());
        $this->originalNode = $originalNode;
        $this->rightScope = $rightScope;
    }
    /**
     * @return BooleanOr|LogicalOr
     */
    public function getOriginalNode()
    {
        return $this->originalNode;
    }
    public function getRightScope() : \PHPStan\Analyser\Scope
    {
        return $this->rightScope;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_BooleanOrNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
