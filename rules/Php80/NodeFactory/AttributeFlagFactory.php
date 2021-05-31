<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
final class AttributeFlagFactory
{
    /**
     * @param ClassConstFetch[] $flags
     * @return \PhpParser\Node\Expr|null
     */
    public function createFlagCollection(array $flags)
    {
        if ($flags === []) {
            return null;
        }
        $flagCollection = \array_shift($flags);
        foreach ($flags as $flag) {
            $flagCollection = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($flagCollection, $flag);
        }
        return $flagCollection;
    }
}
