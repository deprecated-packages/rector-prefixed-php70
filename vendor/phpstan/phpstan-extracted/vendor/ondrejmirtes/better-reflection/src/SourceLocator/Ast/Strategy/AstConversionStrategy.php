<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Ast\Strategy;

use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
/**
 * @internal
 */
interface AstConversionStrategy
{
    /**
     * Take an AST node in some located source (potentially in a namespace) and
     * convert it to something (concrete implementation decides)
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     * @param int|null $positionInNode
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function __invoke(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource, $namespace, $positionInNode = null);
}
