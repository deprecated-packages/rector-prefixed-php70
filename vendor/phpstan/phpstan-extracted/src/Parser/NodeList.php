<?php

declare (strict_types=1);
namespace PHPStan\Parser;

use PhpParser\Node;
class NodeList
{
    /** @var Node */
    private $node;
    /** @var self|null */
    private $next;
    /**
     * @param $this|null $next
     */
    public function __construct(\PhpParser\Node $node, $next = null)
    {
        $this->node = $node;
        $this->next = $next;
    }
    /**
     * @return $this
     */
    public function append(\PhpParser\Node $node)
    {
        $current = $this;
        while ($current->next !== null) {
            $current = $current->next;
        }
        $new = new self($node);
        $current->next = $new;
        return $new;
    }
    public function getNode() : \PhpParser\Node
    {
        return $this->node;
    }
    /**
     * @return $this|null
     */
    public function getNext()
    {
        return $this->next;
    }
}
