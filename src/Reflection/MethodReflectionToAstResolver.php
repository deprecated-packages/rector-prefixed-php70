<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class MethodReflectionToAstResolver
{
    /**
     * @var array<string, array<string, ClassMethod>>
     */
    private $analyzedMethodsInFileName = [];
    /**
     * @var \Rector\FileSystemRector\Parser\FileInfoParser
     */
    private $fileInfoParser;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\FileSystemRector\Parser\FileInfoParser $fileInfoParser, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->fileInfoParser = $fileInfoParser;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    public function resolveProjectClassMethod(\PHPStan\Reflection\Php\PhpMethodReflection $phpMethodReflection)
    {
        $classReflection = $phpMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        if ($fileName === \false) {
            return null;
        }
        // skip vendor
        if (\strpos($fileName, '#\\/vendor\\/#') !== \false) {
            return null;
        }
        $methodName = $phpMethodReflection->getName();
        // skip already anayzed method to prevent cycling
        if (isset($this->analyzedMethodsInFileName[$fileName][$methodName])) {
            return $this->analyzedMethodsInFileName[$fileName][$methodName];
        }
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileName);
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($smartFileInfo);
        /** @var ClassMethod|null $classMethod */
        $classMethod = $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) use($methodName) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $methodName);
        });
        $this->analyzedMethodsInFileName[$fileName][$methodName] = $classMethod;
        return $classMethod;
    }
}
