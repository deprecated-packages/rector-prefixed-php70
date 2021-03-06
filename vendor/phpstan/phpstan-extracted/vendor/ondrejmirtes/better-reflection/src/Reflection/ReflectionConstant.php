<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue;
use PHPStan\BetterReflection\NodeCompiler\CompilerContext;
use PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionConstantStringCast;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\Util\CalculateReflectionColum;
use PHPStan\BetterReflection\Util\ConstantNodeChecker;
use PHPStan\BetterReflection\Util\GetLastDocComment;
use function array_slice;
use function assert;
use function count;
use function explode;
use function implode;
use function substr_count;
class ReflectionConstant implements \PHPStan\BetterReflection\Reflection\Reflection
{
    /** @var Reflector */
    private $reflector;
    /** @var Node\Stmt\Const_|Node\Expr\FuncCall */
    private $node;
    /** @var LocatedSource */
    private $locatedSource;
    /** @var NamespaceNode|null */
    private $declaringNamespace;
    /** @var int|null */
    private $positionInNode;
    /** @var scalar|array<scalar>|null const value */
    private $value;
    /** @var bool */
    private $valueWasCached = \false;
    private function __construct()
    {
    }
    /**
     * Create a ReflectionConstant by name, using default reflectors etc.
     *
     * @throws IdentifierNotFound
     * @return $this
     */
    public static function createFromName(string $constantName)
    {
        return (new \PHPStan\BetterReflection\BetterReflection())->constantReflector()->reflect($constantName);
    }
    /**
     * Create a reflection of a constant
     *
     * @internal
     *
     * @param Node\Stmt\Const_|Node\Expr\FuncCall $node Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     * @return $this
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     * @param int|null $positionInNode
     */
    public static function createFromNode(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource, $namespace = null, $positionInNode = null)
    {
        return $node instanceof \PhpParser\Node\Stmt\Const_ ? self::createFromConstKeyword($reflector, $node, $locatedSource, $namespace, $positionInNode) : self::createFromDefineFunctionCall($reflector, $node, $locatedSource);
    }
    /**
     * @return $this
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespace
     */
    private static function createFromConstKeyword(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node\Stmt\Const_ $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource, $namespace, int $positionInNode)
    {
        $constant = new self();
        $constant->reflector = $reflector;
        $constant->node = $node;
        $constant->locatedSource = $locatedSource;
        $constant->declaringNamespace = $namespace;
        $constant->positionInNode = $positionInNode;
        return $constant;
    }
    /**
     * @throws InvalidConstantNode
     * @return $this
     */
    private static function createFromDefineFunctionCall(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource)
    {
        \PHPStan\BetterReflection\Util\ConstantNodeChecker::assertValidDefineFunctionCall($node);
        $constant = new self();
        $constant->reflector = $reflector;
        $constant->node = $node;
        $constant->locatedSource = $locatedSource;
        return $constant;
    }
    /**
     * Get the "short" name of the constant (e.g. for A\B\FOO, this will return
     * "FOO").
     */
    public function getShortName() : string
    {
        if ($this->node instanceof \PhpParser\Node\Expr\FuncCall) {
            $nameParts = \explode('\\', $this->getNameFromDefineFunctionCall($this->node));
            return $nameParts[\count($nameParts) - 1];
        }
        /** @psalm-suppress PossiblyNullArrayOffset */
        return $this->node->consts[$this->positionInNode]->name->name;
    }
    /**
     * Get the "full" name of the constant (e.g. for A\B\FOO, this will return
     * "A\B\FOO").
     */
    public function getName() : string
    {
        if (!$this->inNamespace()) {
            return $this->getShortName();
        }
        if ($this->node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->getNameFromDefineFunctionCall($this->node);
        }
        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress PossiblyNullArrayOffset
         */
        return $this->node->consts[$this->positionInNode]->namespacedName->toString();
    }
    /**
     * Get the "namespace" name of the constant (e.g. for A\B\FOO, this will
     * return "A\B").
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     */
    public function getNamespaceName() : string
    {
        if (!$this->inNamespace()) {
            return '';
        }
        $namespaceParts = $this->node instanceof \PhpParser\Node\Expr\FuncCall ? \array_slice(\explode('\\', $this->getNameFromDefineFunctionCall($this->node)), 0, -1) : $this->declaringNamespace->name->parts;
        return \implode('\\', $namespaceParts);
    }
    /**
     * Decide if this constant is part of a namespace. Returns false if the constant
     * is in the global namespace or does not have a specified namespace.
     */
    public function inNamespace() : bool
    {
        if ($this->node instanceof \PhpParser\Node\Expr\FuncCall) {
            return \substr_count($this->getNameFromDefineFunctionCall($this->node), '\\') !== 0;
        }
        return $this->declaringNamespace !== null && $this->declaringNamespace->name !== null;
    }
    /**
     * @return string|null
     */
    public function getExtensionName()
    {
        return $this->locatedSource->getExtensionName();
    }
    /**
     * Is this an internal constant?
     */
    public function isInternal() : bool
    {
        return $this->locatedSource->isInternal();
    }
    /**
     * Is this a user-defined function (will always return the opposite of
     * whatever isInternal returns).
     */
    public function isUserDefined() : bool
    {
        return !$this->isInternal();
    }
    /**
     * @param mixed $value
     * @return void
     */
    public function populateValue($value)
    {
        $this->valueWasCached = \true;
        $this->value = $value;
    }
    /**
     * Returns constant value
     *
     * @return scalar|array<scalar>|null
     */
    public function getValue()
    {
        if ($this->valueWasCached !== \false) {
            return $this->value;
        }
        /** @psalm-suppress PossiblyNullArrayOffset */
        $valueNode = $this->node instanceof \PhpParser\Node\Expr\FuncCall ? $this->node->args[1]->value : $this->node->consts[$this->positionInNode]->value;
        $namespace = null;
        if ($this->declaringNamespace !== null && $this->declaringNamespace->name !== null) {
            $namespace = (string) $this->declaringNamespace->name;
        }
        /** @psalm-suppress UndefinedPropertyFetch */
        $this->value = (new \PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue())->__invoke($valueNode, new \PHPStan\BetterReflection\NodeCompiler\CompilerContext($this->reflector, $this->getFileName(), null, $namespace, null));
        $this->valueWasCached = \true;
        return $this->value;
    }
    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->locatedSource->getFileName();
    }
    public function getLocatedSource() : \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource
    {
        return $this->locatedSource;
    }
    /**
     * Get the line number that this constant starts on.
     */
    public function getStartLine() : int
    {
        return $this->node->getStartLine();
    }
    /**
     * Get the line number that this constant ends on.
     */
    public function getEndLine() : int
    {
        return $this->node->getEndLine();
    }
    public function getStartColumn() : int
    {
        return \PHPStan\BetterReflection\Util\CalculateReflectionColum::getStartColumn($this->locatedSource->getSource(), $this->node);
    }
    public function getEndColumn() : int
    {
        return \PHPStan\BetterReflection\Util\CalculateReflectionColum::getEndColumn($this->locatedSource->getSource(), $this->node);
    }
    /**
     * Returns the doc comment for this constant
     */
    public function getDocComment() : string
    {
        return \PHPStan\BetterReflection\Util\GetLastDocComment::forNode($this->node);
    }
    public function __toString() : string
    {
        return \PHPStan\BetterReflection\Reflection\StringCast\ReflectionConstantStringCast::toString($this);
    }
    /**
     * @return Node\Stmt\Const_|Node\Expr\FuncCall
     */
    public function getAst() : \PhpParser\Node
    {
        return $this->node;
    }
    private function getNameFromDefineFunctionCall(\PhpParser\Node\Expr\FuncCall $node) : string
    {
        $nameNode = $node->args[0]->value;
        \assert($nameNode instanceof \PhpParser\Node\Scalar\String_);
        return $nameNode->value;
    }
}
