<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Ast\Strategy;

use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
/**
 * @internal
 */
class NodeToReflection implements \PHPStan\BetterReflection\SourceLocator\Ast\Strategy\AstConversionStrategy
{
    /**
     * Take an AST node in some located source (potentially in a namespace) and
     * convert it to a Reflection
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     * @param int|null $positionInNode
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function __invoke(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource, $namespace, $positionInNode = null)
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromNode($reflector, $node, $locatedSource, $namespace);
        }
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod || $node instanceof \PhpParser\Node\Stmt\Function_ || $node instanceof \PhpParser\Node\Expr\Closure) {
            return \PHPStan\BetterReflection\Reflection\ReflectionFunction::createFromNode($reflector, $node, $locatedSource, $namespace);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Const_) {
            return \PHPStan\BetterReflection\Reflection\ReflectionConstant::createFromNode($reflector, $node, $locatedSource, $namespace, $positionInNode);
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            try {
                return \PHPStan\BetterReflection\Reflection\ReflectionConstant::createFromNode($reflector, $node, $locatedSource);
            } catch (\PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode $e) {
                // Ignore
            }
        }
        return null;
    }
}
