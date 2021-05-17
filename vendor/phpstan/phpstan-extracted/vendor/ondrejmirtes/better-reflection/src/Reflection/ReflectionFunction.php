<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use Closure;
use PhpParser\Node;
use PhpParser\Node\FunctionLike as FunctionNode;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented;
use PHPStan\BetterReflection\Reflection\Exception\FunctionDoesNotExist;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionFunctionStringCast;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\FunctionReflector;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\Type\ClosureSourceLocator;
use function function_exists;
class ReflectionFunction extends \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract implements \PHPStan\BetterReflection\Reflection\Reflection
{
    /**
     * @throws IdentifierNotFound
     * @return $this
     */
    public static function createFromName(string $functionName)
    {
        return (new \PHPStan\BetterReflection\BetterReflection())->functionReflector()->reflect($functionName);
    }
    /**
     * @throws IdentifierNotFound
     * @return $this
     */
    public static function createFromClosure(\Closure $closure)
    {
        $configuration = new \PHPStan\BetterReflection\BetterReflection();
        return (new \PHPStan\BetterReflection\Reflector\FunctionReflector(new \PHPStan\BetterReflection\SourceLocator\Type\ClosureSourceLocator($closure, $configuration->phpParser()), $configuration->classReflector()))->reflect(self::CLOSURE_NAME);
    }
    public function __toString() : string
    {
        return \PHPStan\BetterReflection\Reflection\StringCast\ReflectionFunctionStringCast::toString($this);
    }
    /**
     * @internal
     *
     * @param Node\Stmt\ClassMethod|Node\Stmt\Function_|Node\Expr\Closure $node Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     * @return $this
     * @param \PhpParser\Node\Stmt\Namespace_|null $namespaceNode
     */
    public static function createFromNode(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PhpParser\Node\FunctionLike $node, \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource $locatedSource, $namespaceNode = null)
    {
        $function = new self();
        $function->populateFunctionAbstract($reflector, $node, $locatedSource, $namespaceNode);
        return $function;
    }
    /**
     * Check to see if this function has been disabled (by the PHP INI file
     * directive `disable_functions`).
     *
     * Note - we cannot reflect on internal functions (as there is no PHP source
     * code we can access. This means, at present, we can only EVER return false
     * from this function, because you cannot disable user-defined functions.
     *
     * @see https://php.net/manual/en/ini.core.php#ini.disable-functions
     *
     * @todo https://github.com/Roave/BetterReflection/issues/14
     */
    public function isDisabled() : bool
    {
        return \false;
    }
    /**
     * @throws NotImplemented
     * @throws FunctionDoesNotExist
     */
    public function getClosure() : \Closure
    {
        $this->assertIsNoClosure();
        $functionName = $this->getName();
        $this->assertFunctionExist($functionName);
        return static function (...$args) use($functionName) {
            return $functionName(...$args);
        };
    }
    /**
     * @param mixed ...$args
     *
     * @return mixed
     *
     * @throws NotImplemented
     * @throws FunctionDoesNotExist
     */
    public function invoke(...$args)
    {
        return $this->invokeArgs($args);
    }
    /**
     * @param mixed[] $args
     *
     * @return mixed
     *
     * @throws NotImplemented
     * @throws FunctionDoesNotExist
     */
    public function invokeArgs(array $args = [])
    {
        $this->assertIsNoClosure();
        $functionName = $this->getName();
        $this->assertFunctionExist($functionName);
        return $functionName(...$args);
    }
    /**
     * @throws NotImplemented
     * @return void
     */
    private function assertIsNoClosure()
    {
        if ($this->isClosure()) {
            throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented for closures');
        }
    }
    /**
     * @throws FunctionDoesNotExist
     *
     * @psalm-assert callable-string $functionName
     * @return void
     */
    private function assertFunctionExist(string $functionName)
    {
        if (!\function_exists($functionName)) {
            throw \PHPStan\BetterReflection\Reflection\Exception\FunctionDoesNotExist::fromName($functionName);
        }
    }
}
