<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util;

use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode;
use function count;
use function in_array;
/**
 * @internal
 */
final class ConstantNodeChecker
{
    /**
     * @throws InvalidConstantNode
     * @return void
     */
    public static function assertValidDefineFunctionCall(\PhpParser\Node\Expr\FuncCall $node)
    {
        if (!$node->name instanceof \PhpParser\Node\Name) {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
        if ($node->name->toLowerString() !== 'define') {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
        if (!\in_array(\count($node->args), [2, 3], \true)) {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
        if (!$node->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
        $valueNode = $node->args[1]->value;
        if ($valueNode instanceof \PhpParser\Node\Expr\FuncCall) {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
        if ($valueNode instanceof \PhpParser\Node\Expr\Variable) {
            throw \PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode::create($node);
        }
    }
}
