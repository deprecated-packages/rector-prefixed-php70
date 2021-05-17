<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
class StatementResult
{
    /** @var MutatingScope */
    private $scope;
    /** @var bool */
    private $hasYield;
    /** @var bool */
    private $isAlwaysTerminating;
    /** @var StatementExitPoint[] */
    private $exitPoints;
    /** @var ThrowPoint[] */
    private $throwPoints;
    /**
     * @param MutatingScope $scope
     * @param bool $hasYield
     * @param bool $isAlwaysTerminating
     * @param StatementExitPoint[] $exitPoints
     * @param ThrowPoint[] $throwPoints
     */
    public function __construct(\PHPStan\Analyser\MutatingScope $scope, bool $hasYield, bool $isAlwaysTerminating, array $exitPoints, array $throwPoints)
    {
        $this->scope = $scope;
        $this->hasYield = $hasYield;
        $this->isAlwaysTerminating = $isAlwaysTerminating;
        $this->exitPoints = $exitPoints;
        $this->throwPoints = $throwPoints;
    }
    public function getScope() : \PHPStan\Analyser\MutatingScope
    {
        return $this->scope;
    }
    public function hasYield() : bool
    {
        return $this->hasYield;
    }
    public function isAlwaysTerminating() : bool
    {
        return $this->isAlwaysTerminating;
    }
    /**
     * @return $this
     */
    public function filterOutLoopExitPoints()
    {
        if (!$this->isAlwaysTerminating) {
            return $this;
        }
        foreach ($this->exitPoints as $exitPoint) {
            $statement = $exitPoint->getStatement();
            if (!$statement instanceof \PhpParser\Node\Stmt\Break_ && !$statement instanceof \PhpParser\Node\Stmt\Continue_) {
                continue;
            }
            $num = $statement->num;
            if (!$num instanceof \PhpParser\Node\Scalar\LNumber) {
                return new self($this->scope, $this->hasYield, \false, $this->exitPoints, $this->throwPoints);
            }
            if ($num->value !== 1) {
                continue;
            }
            return new self($this->scope, $this->hasYield, \false, $this->exitPoints, $this->throwPoints);
        }
        return $this;
    }
    /**
     * @return StatementExitPoint[]
     */
    public function getExitPoints() : array
    {
        return $this->exitPoints;
    }
    /**
     * @param class-string<Stmt\Continue_>|class-string<Stmt\Break_> $stmtClass
     * @return StatementExitPoint[]
     */
    public function getExitPointsByType(string $stmtClass) : array
    {
        $exitPoints = [];
        foreach ($this->exitPoints as $exitPoint) {
            $statement = $exitPoint->getStatement();
            if (!$statement instanceof $stmtClass) {
                continue;
            }
            $value = $statement->num;
            if ($value === null) {
                $exitPoints[] = $exitPoint;
                continue;
            }
            if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
                $exitPoints[] = $exitPoint;
                continue;
            }
            $value = $value->value;
            if ($value !== 1) {
                continue;
            }
            $exitPoints[] = $exitPoint;
        }
        return $exitPoints;
    }
    /**
     * @return StatementExitPoint[]
     */
    public function getExitPointsForOuterLoop() : array
    {
        $exitPoints = [];
        foreach ($this->exitPoints as $exitPoint) {
            $statement = $exitPoint->getStatement();
            if (!$statement instanceof \PhpParser\Node\Stmt\Continue_ && !$statement instanceof \PhpParser\Node\Stmt\Break_) {
                continue;
            }
            if ($statement->num === null) {
                continue;
            }
            if (!$statement->num instanceof \PhpParser\Node\Scalar\LNumber) {
                continue;
            }
            $value = $statement->num->value;
            if ($value === 1) {
                continue;
            }
            $newNode = null;
            if ($value > 2) {
                $newNode = new \PhpParser\Node\Scalar\LNumber($value - 1);
            }
            if ($statement instanceof \PhpParser\Node\Stmt\Continue_) {
                $newStatement = new \PhpParser\Node\Stmt\Continue_($newNode);
            } else {
                $newStatement = new \PhpParser\Node\Stmt\Break_($newNode);
            }
            $exitPoints[] = new \PHPStan\Analyser\StatementExitPoint($newStatement, $exitPoint->getScope());
        }
        return $exitPoints;
    }
    /**
     * @return ThrowPoint[]
     */
    public function getThrowPoints() : array
    {
        return $this->throwPoints;
    }
}
