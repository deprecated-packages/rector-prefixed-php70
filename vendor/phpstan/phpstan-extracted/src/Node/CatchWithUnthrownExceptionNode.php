<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeAbstract;
use PHPStan\Type\Type;
class CatchWithUnthrownExceptionNode extends \PhpParser\NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /** @var Catch_ */
    private $originalNode;
    /** @var Type */
    private $caughtType;
    public function __construct(\PhpParser\Node\Stmt\Catch_ $originalNode, \PHPStan\Type\Type $caughtType)
    {
        parent::__construct($originalNode->getAttributes());
        $this->originalNode = $originalNode;
        $this->caughtType = $caughtType;
    }
    public function getOriginalNode() : \PhpParser\Node\Stmt\Catch_
    {
        return $this->originalNode;
    }
    public function getCaughtType() : \PHPStan\Type\Type
    {
        return $this->caughtType;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_CatchWithUnthrownExceptionNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
