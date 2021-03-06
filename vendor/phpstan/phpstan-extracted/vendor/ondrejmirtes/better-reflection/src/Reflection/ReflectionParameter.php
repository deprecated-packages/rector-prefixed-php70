<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use Closure;
use Exception;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param as ParamNode;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue;
use PHPStan\BetterReflection\NodeCompiler\CompilerContext;
use PHPStan\BetterReflection\Reflection\Exception\Uncloneable;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionParameterStringCast;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\Util\CalculateReflectionColum;
use RuntimeException;
use function assert;
use function count;
use function defined;
use function get_class;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function ltrim;
use function sprintf;
use function strtolower;
class ReflectionParameter
{
    /** @var ParamNode */
    private $node;
    /** @var Namespace_|null */
    private $declaringNamespace;
    /** @var ReflectionFunctionAbstract */
    private $function;
    /** @var int */
    private $parameterIndex;
    /** @var scalar|array<scalar>|null */
    private $defaultValue;
    /** @var bool */
    private $isDefaultValueConstant = \false;
    /** @var string|null */
    private $defaultValueConstantName;
    /** @var Reflector */
    private $reflector;
    private function __construct()
    {
    }
    /**
     * Create a reflection of a parameter using a class name
     *
     * @throws OutOfBoundsException
     * @return $this
     */
    public static function createFromClassNameAndMethod(string $className, string $methodName, string $parameterName)
    {
        return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromName($className)->getMethod($methodName)->getParameter($parameterName);
    }
    /**
     * Create a reflection of a parameter using an instance
     *
     * @param object $instance
     *
     * @throws OutOfBoundsException
     * @return $this
     */
    public static function createFromClassInstanceAndMethod($instance, string $methodName, string $parameterName)
    {
        return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromInstance($instance)->getMethod($methodName)->getParameter($parameterName);
    }
    /**
     * Create a reflection of a parameter using a closure
     */
    public static function createFromClosure(\Closure $closure, string $parameterName) : \PHPStan\BetterReflection\Reflection\ReflectionParameter
    {
        return \PHPStan\BetterReflection\Reflection\ReflectionFunction::createFromClosure($closure)->getParameter($parameterName);
    }
    /**
     * Create the parameter from the given spec. Possible $spec parameters are:
     *
     *  - [$instance, 'method']
     *  - ['Foo', 'bar']
     *  - ['foo']
     *  - [function () {}]
     *
     * @param object[]|string[]|string|Closure $spec
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @return $this
     */
    public static function createFromSpec($spec, string $parameterName)
    {
        if (\is_array($spec) && \count($spec) === 2 && \is_string($spec[1])) {
            if (\is_object($spec[0])) {
                return self::createFromClassInstanceAndMethod($spec[0], $spec[1], $parameterName);
            }
            return self::createFromClassNameAndMethod($spec[0], $spec[1], $parameterName);
        }
        if (\is_string($spec)) {
            return \PHPStan\BetterReflection\Reflection\ReflectionFunction::createFromName($spec)->getParameter($parameterName);
        }
        if ($spec instanceof \Closure) {
            return self::createFromClosure($spec, $parameterName);
        }
        throw new \InvalidArgumentException('Could not create reflection from the spec given');
    }
    public function __toString() : string
    {
        return \PHPStan\BetterReflection\Reflection\StringCast\ReflectionParameterStringCast::toString($this);
    }
    /**
     * @internal
     *
     * @param ParamNode       $node               Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     * @param Namespace_|null $declaringNamespace namespace of the declaring function/method
     * @return $this
     */
    public static function createFromNode(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node\Param $node, $declaringNamespace, \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract $function, int $parameterIndex)
    {
        $param = new self();
        $param->reflector = $reflector;
        $param->node = $node;
        $param->declaringNamespace = $declaringNamespace;
        $param->function = $function;
        $param->parameterIndex = $parameterIndex;
        return $param;
    }
    /**
     * @return void
     */
    private function parseDefaultValueNode()
    {
        if (!$this->isDefaultValueAvailable()) {
            throw new \LogicException('This parameter does not have a default value available');
        }
        $defaultValueNode = $this->node->default;
        if ($defaultValueNode instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            \assert($defaultValueNode->class instanceof \PhpParser\Node\Name);
            $className = $defaultValueNode->class->toString();
            \assert($defaultValueNode->name instanceof \PhpParser\Node\Identifier);
            $constantName = $defaultValueNode->name->name;
            if (\strtolower($constantName) !== 'class') {
                if ($className === 'self' || $className === 'static') {
                    $className = $this->findParentClassDeclaringConstant($constantName);
                }
                $this->isDefaultValueConstant = \true;
                \assert($defaultValueNode->name instanceof \PhpParser\Node\Identifier);
                $this->defaultValueConstantName = $className . '::' . $defaultValueNode->name->name;
            }
        }
        $namespace = null;
        if ($this->declaringNamespace !== null && $this->declaringNamespace->name !== null) {
            $namespace = (string) $this->declaringNamespace->name;
        }
        if ($defaultValueNode instanceof \PhpParser\Node\Expr\ConstFetch && !\in_array(\strtolower($defaultValueNode->name->toString()), ['true', 'false', 'null'], \true)) {
            $this->isDefaultValueConstant = \true;
            if ($namespace !== null && !$defaultValueNode->name->isFullyQualified()) {
                $namespacedName = \sprintf('%s\\%s', $namespace, $defaultValueNode->name->toString());
                if (\defined($namespacedName)) {
                    $this->defaultValueConstantName = $namespacedName;
                } else {
                    try {
                        \PHPStan\BetterReflection\Reflection\ReflectionConstant::createFromName($namespacedName);
                        $this->defaultValueConstantName = $namespacedName;
                    } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $e) {
                        // pass
                    }
                }
            }
            if ($this->defaultValueConstantName === null) {
                $this->defaultValueConstantName = $defaultValueNode->name->toString();
            }
        }
        $this->defaultValue = (new \PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue())->__invoke($defaultValueNode, new \PHPStan\BetterReflection\NodeCompiler\CompilerContext($this->reflector, $this->function->getFileName(), $this->getImplementingClass(), $namespace, $this->function->getName()));
    }
    /**
     * @throws LogicException
     */
    private function findParentClassDeclaringConstant(string $constantName) : string
    {
        $method = $this->function;
        \assert($method instanceof \PHPStan\BetterReflection\Reflection\ReflectionMethod);
        $class = $method->getImplementingClass();
        do {
            if ($class->hasConstant($constantName)) {
                return $class->getName();
            }
            $class = $class->getParentClass();
        } while ($class);
        // note: this code is theoretically unreachable, so don't expect any coverage on it
        throw new \LogicException(\sprintf('Failed to find parent class of constant "%s".', $constantName));
    }
    /**
     * Get the name of the parameter.
     */
    public function getName() : string
    {
        \assert(\is_string($this->node->var->name));
        return $this->node->var->name;
    }
    /**
     * Get the function (or method) that declared this parameter.
     */
    public function getDeclaringFunction() : \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract
    {
        return $this->function;
    }
    /**
     * Get the class from the method that this parameter belongs to, if it
     * exists.
     *
     * This will return null if the declaring function is not a method.
     * @return \PHPStan\BetterReflection\Reflection\ReflectionClass|null
     */
    public function getDeclaringClass()
    {
        if ($this->function instanceof \PHPStan\BetterReflection\Reflection\ReflectionMethod) {
            return $this->function->getDeclaringClass();
        }
        return null;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionClass|null
     */
    public function getImplementingClass()
    {
        if ($this->function instanceof \PHPStan\BetterReflection\Reflection\ReflectionMethod) {
            return $this->function->getImplementingClass();
        }
        return null;
    }
    /**
     * Is the parameter optional?
     *
     * Note this is distinct from "isDefaultValueAvailable" because you can have
     * a default value, but the parameter not be optional. In the example, the
     * $foo parameter isOptional() == false, but isDefaultValueAvailable == true
     *
     * @example someMethod($foo = 'foo', $bar)
     */
    public function isOptional() : bool
    {
        return (bool) $this->node->isOptional || $this->isVariadic();
    }
    /**
     * Does the parameter have a default, regardless of whether it is optional.
     *
     * Note this is distinct from "isOptional" because you can have
     * a default value, but the parameter not be optional. In the example, the
     * $foo parameter isOptional() == false, but isDefaultValueAvailable == true
     *
     * @example someMethod($foo = 'foo', $bar)
     */
    public function isDefaultValueAvailable() : bool
    {
        return $this->node->default !== null;
    }
    /**
     * Get the default value of the parameter.
     *
     * @return scalar|array<scalar>|null
     *
     * @throws LogicException
     */
    public function getDefaultValue()
    {
        $this->parseDefaultValueNode();
        return $this->defaultValue;
    }
    /**
     * Does this method allow null for a parameter?
     */
    public function allowsNull() : bool
    {
        if (!$this->hasType()) {
            return \true;
        }
        if ($this->node->type instanceof \PhpParser\Node\NullableType) {
            return \true;
        }
        if (!$this->isDefaultValueAvailable()) {
            return \false;
        }
        return $this->getDefaultValue() === null;
    }
    /**
     * Find the position of the parameter, left to right, starting at zero.
     */
    public function getPosition() : int
    {
        return $this->parameterIndex;
    }
    /**
     * Get the ReflectionType instance representing the type declaration for
     * this parameter
     *
     * (note: this has nothing to do with DocBlocks).
     * @return \PHPStan\BetterReflection\Reflection\ReflectionType|null
     */
    public function getType()
    {
        $type = $this->node->type;
        if ($type === null) {
            return null;
        }
        if (!$type instanceof \PhpParser\Node\NullableType && $this->allowsNull()) {
            $type = new \PhpParser\Node\NullableType($type);
        }
        return \PHPStan\BetterReflection\Reflection\ReflectionType::createFromTypeAndReflector($type);
    }
    /**
     * Does this parameter have a type declaration?
     *
     * (note: this has nothing to do with DocBlocks).
     */
    public function hasType() : bool
    {
        return $this->node->type !== null;
    }
    /**
     * Set the parameter type declaration.
     * @return void
     */
    public function setType(string $newParameterType)
    {
        $this->node->type = new \PhpParser\Node\Name($newParameterType);
    }
    /**
     * Remove the parameter type declaration completely.
     * @return void
     */
    public function removeType()
    {
        $this->node->type = null;
    }
    /**
     * Is this parameter an array?
     */
    public function isArray() : bool
    {
        $type = \ltrim((string) $this->getType(), '?');
        return \strtolower($type) === 'array';
    }
    /**
     * Is this parameter a callable?
     */
    public function isCallable() : bool
    {
        $type = \ltrim((string) $this->getType(), '?');
        return \strtolower($type) === 'callable';
    }
    /**
     * Is this parameter a variadic (denoted by ...$param).
     */
    public function isVariadic() : bool
    {
        return $this->node->variadic;
    }
    /**
     * Is this parameter passed by reference (denoted by &$param).
     */
    public function isPassedByReference() : bool
    {
        return $this->node->byRef;
    }
    public function canBePassedByValue() : bool
    {
        return !$this->isPassedByReference();
    }
    public function isDefaultValueConstant() : bool
    {
        $this->parseDefaultValueNode();
        return $this->isDefaultValueConstant;
    }
    public function isPromoted() : bool
    {
        return $this->node->flags !== 0;
    }
    /**
     * @throws LogicException
     */
    public function getDefaultValueConstantName() : string
    {
        $this->parseDefaultValueNode();
        if (!$this->isDefaultValueConstant()) {
            throw new \LogicException('This parameter is not a constant default value, so cannot have a constant name');
        }
        return $this->defaultValueConstantName;
    }
    /**
     * Gets a ReflectionClass for the type hint (returns null if not a class)
     *
     * @throws RuntimeException
     * @return \PHPStan\BetterReflection\Reflection\ReflectionClass|null
     */
    public function getClass()
    {
        $className = $this->getClassName();
        if ($className === null) {
            return null;
        }
        if (!$this->reflector instanceof \PHPStan\BetterReflection\Reflector\ClassReflector) {
            throw new \RuntimeException(\sprintf('Unable to reflect class type because we were not given a "%s", but a "%s" instead', \PHPStan\BetterReflection\Reflector\ClassReflector::class, \get_class($this->reflector)));
        }
        return $this->reflector->reflect($className);
    }
    /**
     * @return string|null
     */
    private function getClassName()
    {
        if (!$this->hasType()) {
            return null;
        }
        $type = $this->getType();
        if (!$type instanceof \PHPStan\BetterReflection\Reflection\ReflectionNamedType) {
            return null;
        }
        $typeHint = $type->getName();
        if ($typeHint === 'self') {
            $declaringClass = $this->getDeclaringClass();
            \assert($declaringClass instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass);
            return $declaringClass->getName();
        }
        if ($typeHint === 'parent') {
            $declaringClass = $this->getDeclaringClass();
            \assert($declaringClass instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass);
            $parentClass = $declaringClass->getParentClass();
            \assert($parentClass instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass);
            return $parentClass->getName();
        }
        if ($type->isBuiltin()) {
            return null;
        }
        return $typeHint;
    }
    /**
     * {@inheritdoc}
     *
     * @throws Uncloneable
     */
    public function __clone()
    {
        throw \PHPStan\BetterReflection\Reflection\Exception\Uncloneable::fromClass(self::class);
    }
    public function getStartColumn() : int
    {
        return \PHPStan\BetterReflection\Util\CalculateReflectionColum::getStartColumn($this->function->getLocatedSource()->getSource(), $this->node);
    }
    public function getEndColumn() : int
    {
        return \PHPStan\BetterReflection\Util\CalculateReflectionColum::getEndColumn($this->function->getLocatedSource()->getSource(), $this->node);
    }
    public function getAst() : \PhpParser\Node\Param
    {
        return $this->node;
    }
}
