<?php

declare (strict_types=1);
namespace RectorPrefix20210620\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20210620\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ClassMethodNodeNameResolver implements \RectorPrefix20210620\Symplify\Astral\Contract\NodeNameResolverInterface
{
    public function match(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Stmt\ClassMethod;
    }
    /**
     * @param ClassMethod $node
     * @return string|null
     */
    public function resolve(\PhpParser\Node $node)
    {
        return $node->name->toString();
    }
}
