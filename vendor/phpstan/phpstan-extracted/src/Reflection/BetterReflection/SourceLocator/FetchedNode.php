<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceLocator;

/**
 * @template-covariant T of \PhpParser\Node
 */
class FetchedNode
{
    /** @var T */
    private $node;
    /** @var \PhpParser\Node\Stmt\Namespace_|null */
    private $namespace;
    /** @var string */
    private $fileName;
    /**
     * @param T $node
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     * @param string $fileName
     */
    public function __construct(\PhpParser\Node $node, $namespace, string $fileName)
    {
        $this->node = $node;
        $this->namespace = $namespace;
        $this->fileName = $fileName;
    }
    /**
     * @return T
     */
    public function getNode() : \PhpParser\Node
    {
        return $this->node;
    }
    /**
     * @return \PhpParser\Node\Stmt\Namespace_|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function getFileName() : string
    {
        return $this->fileName;
    }
}
