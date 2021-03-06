<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\NodeCompiler\Exception;

use LogicException;
use PhpParser\Node;
use PHPStan\BetterReflection\NodeCompiler\CompilerContext;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use function assert;
use function get_class;
use function sprintf;
class UnableToCompileNode extends \LogicException
{
    /** @var string|null */
    private $constantName;
    /**
     * @return string|null
     */
    public function constantName()
    {
        return $this->constantName;
    }
    /**
     * @return $this
     */
    public static function forUnRecognizedExpressionInContext(\PhpParser\Node\Expr $expression, \PHPStan\BetterReflection\NodeCompiler\CompilerContext $context)
    {
        return new self(\sprintf('Unable to compile expression in %s: unrecognized node type %s at line %d', self::compilerContextToContextDescription($context), \get_class($expression), $expression->getLine()));
    }
    /**
     * @return $this
     */
    public static function becauseOfNotFoundClassConstantReference(\PHPStan\BetterReflection\NodeCompiler\CompilerContext $fetchContext, \PHPStan\BetterReflection\Reflection\ReflectionClass $targetClass, \PhpParser\Node\Expr\ClassConstFetch $constantFetch)
    {
        \assert($constantFetch->name instanceof \PhpParser\Node\Identifier);
        return new self(\sprintf('Could not locate constant %s::%s while trying to evaluate constant expression in %s at line %s', $targetClass->getName(), $constantFetch->name->name, self::compilerContextToContextDescription($fetchContext), $constantFetch->getLine()));
    }
    /**
     * @return $this
     */
    public static function becauseOfNotFoundConstantReference(\PHPStan\BetterReflection\NodeCompiler\CompilerContext $fetchContext, \PhpParser\Node\Expr\ConstFetch $constantFetch)
    {
        $constantName = $constantFetch->name->toString();
        $exception = new self(\sprintf('Could not locate constant "%s" while evaluating expression in %s at line %s', $constantName, self::compilerContextToContextDescription($fetchContext), $constantFetch->getLine()));
        $exception->constantName = $constantName;
        return $exception;
    }
    private static function compilerContextToContextDescription(\PHPStan\BetterReflection\NodeCompiler\CompilerContext $fetchContext) : string
    {
        // @todo improve in https://github.com/Roave/BetterReflection/issues/434
        return $fetchContext->hasSelf() ? $fetchContext->getSelf()->getName() : 'unknown context (probably a function)';
    }
}
