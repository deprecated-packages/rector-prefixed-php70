<?php

declare (strict_types=1);
namespace RectorPrefix20210620\Symplify\Astral\NodeFinder;

use PhpParser\Node;
use PhpParser\NodeFinder;
use RectorPrefix20210620\Symplify\Astral\ValueObject\AttributeKey;
use RectorPrefix20210620\Symplify\PackageBuilder\Php\TypeChecker;
final class SimpleNodeFinder
{
    /**
     * @var TypeChecker
     */
    private $typeChecker;
    /**
     * @var NodeFinder
     */
    private $nodeFinder;
    public function __construct(\RectorPrefix20210620\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \PhpParser\NodeFinder $nodeFinder)
    {
        $this->typeChecker = $typeChecker;
        $this->nodeFinder = $nodeFinder;
    }
    /**
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return T[]
     */
    public function findByType(\PhpParser\Node $node, string $nodeClass) : array
    {
        return $this->nodeFinder->findInstanceOf($node, $nodeClass);
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $nodeClasses
     */
    public function hasByTypes(\PhpParser\Node $node, array $nodeClasses) : bool
    {
        foreach ($nodeClasses as $nodeClass) {
            if ($this->findByType($node, $nodeClass)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @see https://phpstan.org/blog/generics-in-php-using-phpdocs for template
     *
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return \PhpParser\Node|null
     */
    public function findFirstParentByType(\PhpParser\Node $node, string $nodeClass)
    {
        $node = $node->getAttribute(\RectorPrefix20210620\Symplify\Astral\ValueObject\AttributeKey::PARENT);
        while ($node) {
            if (\is_a($node, $nodeClass, \true)) {
                return $node;
            }
            $node = $node->getAttribute(\RectorPrefix20210620\Symplify\Astral\ValueObject\AttributeKey::PARENT);
        }
        return null;
    }
    /**
     * @template T of Node
     * @param class-string<T>[] $nodeTypes
     * @return \PhpParser\Node|null
     */
    public function findFirstParentByTypes(\PhpParser\Node $node, array $nodeTypes)
    {
        $node = $node->getAttribute(\RectorPrefix20210620\Symplify\Astral\ValueObject\AttributeKey::PARENT);
        while ($node) {
            if ($this->typeChecker->isInstanceOf($node, $nodeTypes)) {
                return $node;
            }
            $node = $node->getAttribute(\RectorPrefix20210620\Symplify\Astral\ValueObject\AttributeKey::PARENT);
        }
        return null;
    }
}
