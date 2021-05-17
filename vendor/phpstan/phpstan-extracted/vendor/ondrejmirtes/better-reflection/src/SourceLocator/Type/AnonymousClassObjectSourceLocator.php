<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Exception\EvaledAnonymousClassCannotBeLocated;
use PHPStan\BetterReflection\SourceLocator\Exception\TwoAnonymousClassesOnSameLine;
use PHPStan\BetterReflection\SourceLocator\FileChecker;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\Util\FileHelper;
use ReflectionClass as CoreReflectionClass;
use ReflectionException;
use function array_filter;
use function array_values;
use function assert;
use function file_get_contents;
use function gettype;
use function is_object;
use function sprintf;
use function strpos;
/**
 * @internal
 */
final class AnonymousClassObjectSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var CoreReflectionClass */
    private $coreClassReflection;
    /** @var Parser */
    private $parser;
    /**
     * @param object $anonymousClassObject
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     *
     * @psalm-suppress DocblockTypeContradiction
     */
    public function __construct($anonymousClassObject, \PhpParser\Parser $parser)
    {
        if (!\is_object($anonymousClassObject)) {
            throw new \InvalidArgumentException(\sprintf('Can only create from an instance of an object, "%s" given', \gettype($anonymousClassObject)));
        }
        $this->coreClassReflection = new \ReflectionClass($anonymousClassObject);
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
        return $this->getReflectionClass($reflector, $identifier->getType());
    }
    /**
     * {@inheritDoc}
     *
     * @throws ParseToAstFailure
     */
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return \array_filter([$this->getReflectionClass($reflector, $identifierType)]);
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionClass|null
     */
    private function getReflectionClass(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType)
    {
        if (!$identifierType->isClass()) {
            return null;
        }
        $fileName = $this->coreClassReflection->getFileName();
        if (\strpos($fileName, 'eval()\'d code') !== \false) {
            throw \PHPStan\BetterReflection\SourceLocator\Exception\EvaledAnonymousClassCannotBeLocated::create();
        }
        \PHPStan\BetterReflection\SourceLocator\FileChecker::assertReadableFile($fileName);
        $fileName = \PHPStan\BetterReflection\Util\FileHelper::normalizeWindowsPath($fileName);
        $nodeVisitor = new class($fileName, $this->coreClassReflection->getStartLine()) extends \PhpParser\NodeVisitorAbstract
        {
            /** @var string */
            private $fileName;
            /** @var int */
            private $startLine;
            /** @var Class_[] */
            private $anonymousClassNodes = [];
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
                if (!$node instanceof \PhpParser\Node\Stmt\Class_ || $node->name !== null) {
                    return null;
                }
                $this->anonymousClassNodes[] = $node;
                return null;
            }
            /**
             * @return \PhpParser\Node\Stmt\Class_|null
             */
            public function getAnonymousClassNode()
            {
                /** @var Class_[] $anonymousClassNodesOnSameLine */
                $anonymousClassNodesOnSameLine = \array_values(\array_filter($this->anonymousClassNodes, function (\PhpParser\Node\Stmt\Class_ $node) : bool {
                    return $node->getLine() === $this->startLine;
                }));
                if (!$anonymousClassNodesOnSameLine) {
                    return null;
                }
                if (isset($anonymousClassNodesOnSameLine[1])) {
                    throw \PHPStan\BetterReflection\SourceLocator\Exception\TwoAnonymousClassesOnSameLine::create($this->fileName, $this->startLine);
                }
                return $anonymousClassNodesOnSameLine[0];
            }
        };
        $fileContents = \file_get_contents($fileName);
        $ast = $this->parser->parse($fileContents);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($nodeVisitor);
        $nodeTraverser->traverse($ast);
        $reflectionClass = (new \PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection())->__invoke($reflector, $nodeVisitor->getAnonymousClassNode(), new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource($fileContents, $fileName), null);
        \assert($reflectionClass instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass || $reflectionClass === null);
        return $reflectionClass;
    }
}
