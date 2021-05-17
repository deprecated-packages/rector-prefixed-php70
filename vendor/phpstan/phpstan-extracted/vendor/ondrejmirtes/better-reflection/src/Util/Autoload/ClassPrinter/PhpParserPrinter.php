<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\ClassPrinter;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\PrettyPrinter\Standard as CodePrinter;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
final class PhpParserPrinter implements \PHPStan\BetterReflection\Util\Autoload\ClassPrinter\ClassPrinterInterface
{
    public function __invoke(\PHPStan\BetterReflection\Reflection\ReflectionClass $classInfo) : string
    {
        $nodes = [];
        if ($classInfo->inNamespace()) {
            $nodes[] = new \PhpParser\Node\Stmt\Namespace_(new \PhpParser\Node\Name($classInfo->getNamespaceName()));
        }
        $nodes[] = $classInfo->getAst();
        return (new \PhpParser\PrettyPrinter\Standard())->prettyPrint($nodes);
    }
}
