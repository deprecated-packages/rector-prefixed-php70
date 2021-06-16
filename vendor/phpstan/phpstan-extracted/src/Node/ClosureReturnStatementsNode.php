<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\StatementResult;
/** @api */
class ClosureReturnStatementsNode extends \PhpParser\NodeAbstract implements \PHPStan\Node\ReturnStatementsNode
{
    /** @var \PhpParser\Node\Expr\Closure */
    private $closureExpr;
    /** @var \PHPStan\Node\ReturnStatement[] */
    private $returnStatements;
    /** @var array<int, Yield_|YieldFrom> */
    private $yieldStatements;
    /** @var StatementResult */
    private $statementResult;
    /**
     * @param \PhpParser\Node\Expr\Closure $closureExpr
     * @param \PHPStan\Node\ReturnStatement[] $returnStatements
     * @param array<int, Yield_|YieldFrom> $yieldStatements
     * @param \PHPStan\Analyser\StatementResult $statementResult
     */
    public function __construct(\PhpParser\Node\Expr\Closure $closureExpr, array $returnStatements, array $yieldStatements, \PHPStan\Analyser\StatementResult $statementResult)
    {
        parent::__construct($closureExpr->getAttributes());
        $this->closureExpr = $closureExpr;
        $this->returnStatements = $returnStatements;
        $this->yieldStatements = $yieldStatements;
        $this->statementResult = $statementResult;
    }
    public function getClosureExpr() : \PhpParser\Node\Expr\Closure
    {
        return $this->closureExpr;
    }
    /**
     * @return \PHPStan\Node\ReturnStatement[]
     */
    public function getReturnStatements() : array
    {
        return $this->returnStatements;
    }
    /**
     * @return array<int, Yield_|YieldFrom>
     */
    public function getYieldStatements() : array
    {
        return $this->yieldStatements;
    }
    public function getStatementResult() : \PHPStan\Analyser\StatementResult
    {
        return $this->statementResult;
    }
    public function returnsByRef() : bool
    {
        return $this->closureExpr->byRef;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_ClosureReturnStatementsNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
