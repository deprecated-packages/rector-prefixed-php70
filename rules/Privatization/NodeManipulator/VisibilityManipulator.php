<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\InvalidNodeTypeException;
use Rector\Core\ValueObject\Visibility;
use RectorPrefix20210620\Webmozart\Assert\Assert;
final class VisibilityManipulator
{
    /**
     * @var array<class-string<Stmt>>
     */
    const ALLOWED_NODE_TYPES = [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassConst::class, \PhpParser\Node\Stmt\Class_::class];
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function makeStatic(\PhpParser\Node $node)
    {
        $this->addVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::STATIC);
    }
    /**
     * @param ClassMethod|Class_ $node
     * @return void
     */
    public function makeAbstract(\PhpParser\Node $node)
    {
        $this->addVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::ABSTRACT);
    }
    /**
     * @param ClassMethod|Property $node
     * @return void
     */
    public function makeNonStatic(\PhpParser\Node $node)
    {
        if (!$node->isStatic()) {
            return;
        }
        $node->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC;
    }
    /**
     * @param Class_|ClassMethod $node
     * @return void
     */
    public function makeFinal(\PhpParser\Node $node)
    {
        $this->addVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::FINAL);
    }
    /**
     * @param Class_|ClassMethod $node
     * @return void
     */
    public function makeNonFinal(\PhpParser\Node $node)
    {
        if (!$node->isFinal()) {
            return;
        }
        $node->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL;
    }
    /**
     * This way "abstract", "static", "final" are kept
     *
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function removeVisibility(\PhpParser\Node $node)
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);
        // no modifier
        if ($node->flags === 0) {
            return;
        }
        if ($node->isPublic()) {
            $node->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC;
        }
        if ($node->isProtected()) {
            $node->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED;
        }
        if ($node->isPrivate()) {
            $node->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
        }
    }
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function changeNodeVisibility(\PhpParser\Node $node, int $visibility)
    {
        \RectorPrefix20210620\Webmozart\Assert\Assert::oneOf($visibility, [\Rector\Core\ValueObject\Visibility::PUBLIC, \Rector\Core\ValueObject\Visibility::PROTECTED, \Rector\Core\ValueObject\Visibility::PRIVATE, \Rector\Core\ValueObject\Visibility::STATIC, \Rector\Core\ValueObject\Visibility::ABSTRACT, \Rector\Core\ValueObject\Visibility::FINAL]);
        $this->replaceVisibilityFlag($node, $visibility);
    }
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function makePublic(\PhpParser\Node $node)
    {
        $this->replaceVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::PUBLIC);
    }
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function makeProtected(\PhpParser\Node $node)
    {
        $this->replaceVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::PROTECTED);
    }
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    public function makePrivate(\PhpParser\Node $node)
    {
        $this->replaceVisibilityFlag($node, \Rector\Core\ValueObject\Visibility::PRIVATE);
    }
    /**
     * @return void
     */
    public function removeFinal(\PhpParser\Node\Stmt\Class_ $class)
    {
        $class->flags -= \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL;
    }
    /**
     * @param Class_|ClassMethod|Property|ClassConst $node
     * @return void
     */
    private function addVisibilityFlag(\PhpParser\Node $node, int $visibility)
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);
        $node->flags |= $visibility;
    }
    /**
     * @return void
     */
    private function ensureIsClassMethodOrProperty(\PhpParser\Node $node, string $location)
    {
        foreach (self::ALLOWED_NODE_TYPES as $allowedNodeType) {
            if (\is_a($node, $allowedNodeType, \true)) {
                return;
            }
        }
        throw new \Rector\Core\Exception\InvalidNodeTypeException(\sprintf('"%s" only accepts "%s" types. "%s" given.', $location, \implode('", "', self::ALLOWED_NODE_TYPES), \get_class($node)));
    }
    /**
     * @param ClassMethod|Property|ClassConst $node
     * @return void
     */
    private function replaceVisibilityFlag(\PhpParser\Node $node, int $visibility)
    {
        $isStatic = $node instanceof \PhpParser\Node\Stmt\ClassMethod && $node->isStatic();
        if ($isStatic) {
            $this->removeVisibility($node);
        }
        if ($visibility !== \Rector\Core\ValueObject\Visibility::STATIC && $visibility !== \Rector\Core\ValueObject\Visibility::ABSTRACT && $visibility !== \Rector\Core\ValueObject\Visibility::FINAL) {
            $this->removeVisibility($node);
        }
        $this->addVisibilityFlag($node, $visibility);
        if ($isStatic) {
            $this->makeStatic($node);
        }
    }
}
