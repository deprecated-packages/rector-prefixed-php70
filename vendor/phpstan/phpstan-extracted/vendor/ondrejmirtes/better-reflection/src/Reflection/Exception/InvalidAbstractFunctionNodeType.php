<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use InvalidArgumentException;
use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract;
use function get_class;
use function sprintf;
class InvalidAbstractFunctionNodeType extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function fromNode(\PhpParser\Node $node)
    {
        return new self(\sprintf('Node for "%s" must be "%s" or "%s", was a "%s"', \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\FunctionLike::class, \get_class($node)));
    }
}
