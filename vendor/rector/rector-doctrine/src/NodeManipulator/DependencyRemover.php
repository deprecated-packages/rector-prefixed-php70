<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use RectorPrefix20210620\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class DependencyRemover
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20210620\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeRemoval\NodeRemover $nodeRemover)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeRemover = $nodeRemover;
    }
    /**
     * @return void
     */
    public function removeByType(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $registryParam, string $type)
    {
        // remove constructor param: $managerRegistry
        foreach ($classMethod->params as $key => $param) {
            if ($param->type === null) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($param->type, $type)) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
        $this->removeRegistryDependencyAssign($class, $classMethod, $registryParam);
    }
    /**
     * @return void
     */
    private function removeRegistryDependencyAssign(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $registryParam)
    {
        foreach ((array) $classMethod->stmts as $constructorMethodStmt) {
            if (!$constructorMethodStmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$constructorMethodStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            /** @var Assign $assign */
            $assign = $constructorMethodStmt->expr;
            if (!$this->nodeNameResolver->areNamesEqual($assign->expr, $registryParam->var)) {
                continue;
            }
            $this->removeManagerRegistryProperty($class, $assign);
            // remove assign
            $this->nodeRemover->removeNodeFromStatements($classMethod, $constructorMethodStmt);
            break;
        }
    }
    /**
     * @return void
     */
    private function removeManagerRegistryProperty(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\Assign $assign)
    {
        $removedPropertyName = $this->nodeNameResolver->getName($assign->var);
        if ($removedPropertyName === null) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($removedPropertyName) {
            if (!$node instanceof \PhpParser\Node\Stmt\Property) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $removedPropertyName)) {
                return null;
            }
            $this->nodeRemover->removeNode($node);
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
    }
}
