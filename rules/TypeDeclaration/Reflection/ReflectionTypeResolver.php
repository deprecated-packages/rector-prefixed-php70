<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Reflection;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20210620\Symplify\PackageBuilder\Reflection\PrivatesCaller;
final class ReflectionTypeResolver
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesCaller
     */
    private $privatesCaller;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20210620\Symplify\PackageBuilder\Reflection\PrivatesCaller $privatesCaller)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->privatesCaller = $privatesCaller;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolveMethodCallReturnType(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $objectType = $this->nodeTypeResolver->resolve($methodCall->var);
        if (!$objectType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }
        return $this->resolveNativeReturnTypeFromClassAndMethod($objectType->getClassName(), $methodName, $methodCall);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolveStaticCallReturnType(\PhpParser\Node\Expr\StaticCall $staticCall)
    {
        $className = $this->nodeNameResolver->getName($staticCall->class);
        if ($className === null) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }
        return $this->resolveNativeReturnTypeFromClassAndMethod($className, $methodName, $staticCall);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolvePropertyFetchType(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $objectType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (!$objectType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        if ($propertyName === null) {
            return null;
        }
        if ($classReflection->hasProperty($propertyName)) {
            $propertyFetchScope = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);
            if ($propertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
                return $propertyReflection->getNativeType();
            }
        }
        return null;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolveFuncCallReturnType(\PhpParser\Node\Expr\FuncCall $funcCall)
    {
        $funcCallScope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $funcCallName = $funcCall->name;
        if ($funcCallName instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$this->reflectionProvider->hasFunction($funcCallName, $funcCallScope)) {
            return null;
        }
        $functionReflection = $this->reflectionProvider->getFunction($funcCallName, $funcCallScope);
        if (!$functionReflection instanceof \PHPStan\Reflection\Php\PhpFunctionReflection) {
            return null;
        }
        return $this->privatesCaller->callPrivateMethod($functionReflection, 'getNativeReturnType', []);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    private function resolveNativeReturnTypeFromClassAndMethod(string $className, string $methodName, \PhpParser\Node\Expr $expr)
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasMethod($methodName)) {
            return null;
        }
        $callerScope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // probably trait
        if (!$callerScope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $methodReflection = $classReflection->getMethod($methodName, $callerScope);
        if (!$methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        return $this->privatesCaller->callPrivateMethod($methodReflection, 'getNativeReturnType', []);
    }
}
