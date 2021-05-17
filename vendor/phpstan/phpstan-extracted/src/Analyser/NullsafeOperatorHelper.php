<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
class NullsafeOperatorHelper
{
    public static function getNullsafeShortcircuitedExpr(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if ($expr instanceof \PhpParser\Node\Expr\NullsafeMethodCall) {
            return new \PhpParser\Node\Expr\MethodCall(self::getNullsafeShortcircuitedExpr($expr->var), $expr->name, $expr->args);
        }
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            $var = self::getNullsafeShortcircuitedExpr($expr->var);
            if ($expr->var === $var) {
                return $expr;
            }
            return new \PhpParser\Node\Expr\MethodCall($var, $expr->name, $expr->args);
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticCall && $expr->class instanceof \PhpParser\Node\Expr) {
            $class = self::getNullsafeShortcircuitedExpr($expr->class);
            if ($expr->class === $class) {
                return $expr;
            }
            return new \PhpParser\Node\Expr\StaticCall($class, $expr->name, $expr->args);
        }
        if ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $var = self::getNullsafeShortcircuitedExpr($expr->var);
            if ($expr->var === $var) {
                return $expr;
            }
            return new \PhpParser\Node\Expr\ArrayDimFetch($var, $expr->dim);
        }
        if ($expr instanceof \PhpParser\Node\Expr\NullsafePropertyFetch) {
            return new \PhpParser\Node\Expr\PropertyFetch(self::getNullsafeShortcircuitedExpr($expr->var), $expr->name);
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $var = self::getNullsafeShortcircuitedExpr($expr->var);
            if ($expr->var === $var) {
                return $expr;
            }
            return new \PhpParser\Node\Expr\PropertyFetch($var, $expr->name);
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch && $expr->class instanceof \PhpParser\Node\Expr) {
            $class = self::getNullsafeShortcircuitedExpr($expr->class);
            if ($expr->class === $class) {
                return $expr;
            }
            return new \PhpParser\Node\Expr\StaticPropertyFetch($class, $expr->name);
        }
        return $expr;
    }
}
