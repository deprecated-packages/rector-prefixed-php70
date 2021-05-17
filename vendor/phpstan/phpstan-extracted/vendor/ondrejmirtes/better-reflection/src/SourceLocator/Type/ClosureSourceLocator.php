<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Exception\EvaledClosureCannotBeLocated;
use PHPStan\BetterReflection\SourceLocator\Exception\TwoClosuresOnSameLine;
use PHPStan\BetterReflection\SourceLocator\FileChecker;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\Util\FileHelper;
use ReflectionFunction as CoreFunctionReflection;
use function array_filter;
use function array_values;
use function assert;
use function file_get_contents;
use function is_array;
use function strpos;
/**
 * @internal
 */
final class ClosureSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var CoreFunctionReflection */
    private $coreFunctionReflection;
    /** @var Parser */
    private $parser;
    public function __construct(\Closure $closure, \PhpParser\Parser $parser)
    {
        $this->coreFunctionReflection = new \ReflectionFunction($closure);
        $this->parser = $parser;
    }
    /**
     * {@inheritDoc}
     *
     * @throws ParseToAstFailure
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        return $this->getReflectionFunction($reflector, $identifier->getType());
    }
    /**
     * {@inheritDoc}
     *
     * @throws ParseToAstFailure
     */
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return \array_filter([$this->getReflectionFunction($reflector, $identifierType)]);
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionFunction|null
     */
    private function getReflectionFunction(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType)
    {
        if (!$identifierType->isFunction()) {
            return null;
        }
        $fileName = $this->coreFunctionReflection->getFileName();
        if (\strpos($fileName, 'eval()\'d code') !== \false) {
            throw \PHPStan\BetterReflection\SourceLocator\Exception\EvaledClosureCannotBeLocated::create();
        }
        \PHPStan\BetterReflection\SourceLocator\FileChecker::assertReadableFile($fileName);
        $fileName = \PHPStan\BetterReflection\Util\FileHelper::normalizeWindowsPath($fileName);
        $nodeVisitor = new class($fileName, $this->coreFunctionReflection->getStartLine()) extends \PhpParser\NodeVisitorAbstract
        {
            /** @var string */
            private $fileName;
            /** @var int */
            private $startLine;
            /** @var (Node|null)[][] */
            private $closureNodes = [];
            /** @var Namespace_|null */
            private $currentNamespace;
            public function __construct(string $fileName, int $startLine)
            {
                $this->fileName = $fileName;
                $this->startLine = $startLine;
            }
            /**
             * {@inheritDoc}
             */
            public function enterNode(\PhpParser\Node $node)
            {
                if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
                    $this->currentNamespace = $node;
                    return null;
                }
                if (!$node instanceof \PhpParser\Node\Expr\Closure) {
                    return null;
                }
                $this->closureNodes[] = [$node, $this->currentNamespace];
                return null;
            }
            /**
             * {@inheritDoc}
             */
            public function leaveNode(\PhpParser\Node $node)
            {
                if (!$node instanceof \PhpParser\Node\Stmt\Namespace_) {
                    return null;
                }
                $this->currentNamespace = null;
                return null;
            }
            /**
             * @return mixed[]|null
             *
             * @throws TwoClosuresOnSameLine
             */
            public function getClosureNodes()
            {
                /** @var (Node|null)[][] $closureNodesDataOnSameLine */
                $closureNodesDataOnSameLine = \array_values(\array_filter($this->closureNodes, function (array $nodes) : bool {
                    return $nodes[0]->getLine() === $this->startLine;
                }));
                if (!$closureNodesDataOnSameLine) {
                    return null;
                }
                if (isset($closureNodesDataOnSameLine[1])) {
                    throw \PHPStan\BetterReflection\SourceLocator\Exception\TwoClosuresOnSameLine::create($this->fileName, $this->startLine);
                }
                return $closureNodesDataOnSameLine[0];
            }
        };
        $fileContents = \file_get_contents($fileName);
        $ast = $this->parser->parse($fileContents);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($nodeVisitor);
        $nodeTraverser->traverse($ast);
        $closureNodes = $nodeVisitor->getClosureNodes();
        \assert(\is_array($closureNodes));
        \assert($closureNodes[1] instanceof \PhpParser\Node\Stmt\Namespace_ || $closureNodes[1] === null);
        $reflectionFunction = (new \PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection())->__invoke($reflector, $closureNodes[0], new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource($fileContents, $fileName), $closureNodes[1]);
        \assert($reflectionFunction instanceof \PHPStan\BetterReflection\Reflection\ReflectionFunction || $reflectionFunction === null);
        return $reflectionFunction;
    }
}
