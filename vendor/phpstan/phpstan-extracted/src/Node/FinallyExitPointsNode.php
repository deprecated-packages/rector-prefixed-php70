<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\NodeAbstract;
use PHPStan\Analyser\StatementExitPoint;
/** @api */
class FinallyExitPointsNode extends \PhpParser\NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /** @var StatementExitPoint[] */
    private $finallyExitPoints;
    /** @var StatementExitPoint[] */
    private $tryCatchExitPoints;
    /**
     * @param StatementExitPoint[] $finallyExitPoints
     * @param StatementExitPoint[] $tryCatchExitPoints
     */
    public function __construct(array $finallyExitPoints, array $tryCatchExitPoints)
    {
        parent::__construct([]);
        $this->finallyExitPoints = $finallyExitPoints;
        $this->tryCatchExitPoints = $tryCatchExitPoints;
    }
    /**
     * @return StatementExitPoint[]
     */
    public function getFinallyExitPoints() : array
    {
        return $this->finallyExitPoints;
    }
    /**
     * @return StatementExitPoint[]
     */
    public function getTryCatchExitPoints() : array
    {
        return $this->tryCatchExitPoints;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_FinallyExitPointsNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
