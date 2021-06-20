<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20210620\Symfony\Contracts\Service\Attribute\Required;
final class ParamNameResolver implements \Rector\NodeNameResolver\Contract\NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @return void
     */
    #[Required]
    public function autowireParamNameResolver(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return class-string<Node>
     */
    public function getNode() : string
    {
        return \PhpParser\Node\Param::class;
    }
    /**
     * @param Param $node
     * @return string|null
     */
    public function resolve(\PhpParser\Node $node)
    {
        return $this->nodeNameResolver->getName($node->var);
    }
}
