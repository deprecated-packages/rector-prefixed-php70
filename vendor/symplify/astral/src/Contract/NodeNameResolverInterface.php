<?php

declare (strict_types=1);
namespace RectorPrefix20210525\Symplify\Astral\Contract;

use PhpParser\Node;
interface NodeNameResolverInterface
{
    public function match(\PhpParser\Node $node) : bool;
    /**
     * @return string|null
     */
    public function resolve(\PhpParser\Node $node);
}
