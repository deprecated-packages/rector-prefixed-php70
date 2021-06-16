<?php

declare (strict_types=1);
namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchKind;
final class SwitchExprsResolver
{
    /**
     * @return CondAndExpr[]
     */
    public function resolve(\PhpParser\Node\Stmt\Switch_ $switch) : array
    {
        $condAndExpr = [];
        $previousCondExpr = null;
        foreach ($switch->cases as $case) {
            if (!$this->isValidCase($case)) {
                return [];
            }
            // prepend to previous one
            if ($case->stmts === []) {
                $previousCondExpr = $case->cond;
                continue;
            }
            $expr = $case->stmts[0];
            if ($expr instanceof \PhpParser\Node\Stmt\Expression) {
                $expr = $expr->expr;
            }
            $condExprs = [];
            if ($previousCondExpr instanceof \PhpParser\Node\Expr) {
                $condExprs[] = $previousCondExpr;
                $previousCondExpr = null;
            }
            if ($case->cond !== null) {
                $condExprs[] = $case->cond;
            }
            if ($expr instanceof \PhpParser\Node\Stmt\Return_) {
                $returnedExpr = $expr->expr;
                if (!$returnedExpr instanceof \PhpParser\Node\Expr) {
                    return [];
                }
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($condExprs, $returnedExpr, \Rector\Php80\ValueObject\MatchKind::RETURN);
            } elseif ($expr instanceof \PhpParser\Node\Expr\Assign) {
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($condExprs, $expr, \Rector\Php80\ValueObject\MatchKind::ASSIGN);
            } elseif ($expr instanceof \PhpParser\Node\Expr) {
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($condExprs, $expr, \Rector\Php80\ValueObject\MatchKind::NORMAL);
            } elseif ($expr instanceof \PhpParser\Node\Stmt\Throw_) {
                $throwExpr = new \PhpParser\Node\Expr\Throw_($expr->expr);
                $condAndExpr[] = new \Rector\Php80\ValueObject\CondAndExpr($condExprs, $throwExpr, \Rector\Php80\ValueObject\MatchKind::THROW);
            } else {
                return [];
            }
        }
        return $condAndExpr;
    }
    private function isValidCase(\PhpParser\Node\Stmt\Case_ $case) : bool
    {
        // prepend to previous one
        if ($case->stmts === []) {
            return \true;
        }
        if (\count($case->stmts) === 2 && $case->stmts[1] instanceof \PhpParser\Node\Stmt\Break_) {
            return \true;
        }
        // default throws stmts
        if (\count($case->stmts) !== 1) {
            return \false;
        }
        // throws expressoin
        if ($case->stmts[0] instanceof \PhpParser\Node\Stmt\Throw_) {
            return \true;
        }
        // instant return
        if ($case->stmts[0] instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        // default value
        return $case->cond === null;
    }
}
