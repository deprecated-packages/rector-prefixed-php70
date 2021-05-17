<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\ReflectionProvider;
use function array_key_exists;
class TypeAliasResolver
{
    /** @var array<string, string> */
    private $globalTypeAliases;
    /** @var TypeStringResolver */
    private $typeStringResolver;
    /** @var TypeNodeResolver */
    private $typeNodeResolver;
    /** @var ReflectionProvider */
    private $reflectionProvider;
    /** @var array<string, Type> */
    private $resolvedGlobalTypeAliases = [];
    /** @var array<string, Type> */
    private $resolvedLocalTypeAliases = [];
    /** @var array<string, true> */
    private $resolvingClassTypeAliases = [];
    /** @var array<string, true> */
    private $inProcess = [];
    /**
     * @param array<string, string> $globalTypeAliases
     */
    public function __construct(array $globalTypeAliases, \PHPStan\PhpDoc\TypeStringResolver $typeStringResolver, \PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->globalTypeAliases = $globalTypeAliases;
        $this->typeStringResolver = $typeStringResolver;
        $this->typeNodeResolver = $typeNodeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param string|null $classNameScope
     */
    public function hasTypeAlias(string $aliasName, $classNameScope) : bool
    {
        $hasGlobalTypeAlias = \array_key_exists($aliasName, $this->globalTypeAliases);
        if ($hasGlobalTypeAlias) {
            return \true;
        }
        if ($classNameScope === null || !$this->reflectionProvider->hasClass($classNameScope)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($classNameScope);
        $localTypeAliases = $classReflection->getTypeAliases();
        return \array_key_exists($aliasName, $localTypeAliases);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolveTypeAlias(string $aliasName, \PHPStan\Analyser\NameScope $nameScope)
    {
        return $this->resolveLocalTypeAlias($aliasName, $nameScope) ?? $this->resolveGlobalTypeAlias($aliasName, $nameScope);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    private function resolveLocalTypeAlias(string $aliasName, \PHPStan\Analyser\NameScope $nameScope)
    {
        if (\array_key_exists($aliasName, $this->globalTypeAliases)) {
            return null;
        }
        if (!$nameScope->hasTypeAlias($aliasName)) {
            return null;
        }
        $className = $nameScope->getClassName();
        if ($className === null) {
            return null;
        }
        $aliasNameInClassScope = $className . '::' . $aliasName;
        if (\array_key_exists($aliasNameInClassScope, $this->resolvedLocalTypeAliases)) {
            return $this->resolvedLocalTypeAliases[$aliasNameInClassScope];
        }
        // prevent infinite recursion
        if (\array_key_exists($className, $this->resolvingClassTypeAliases)) {
            return null;
        }
        $this->resolvingClassTypeAliases[$className] = \true;
        if (!$this->reflectionProvider->hasClass($className)) {
            unset($this->resolvingClassTypeAliases[$className]);
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $localTypeAliases = $classReflection->getTypeAliases();
        unset($this->resolvingClassTypeAliases[$className]);
        if (!\array_key_exists($aliasName, $localTypeAliases)) {
            return null;
        }
        if (\array_key_exists($aliasNameInClassScope, $this->inProcess)) {
            // resolve circular reference as ErrorType to make it easier to detect
            throw new \PHPStan\Type\CircularTypeAliasDefinitionException();
        }
        $this->inProcess[$aliasNameInClassScope] = \true;
        try {
            $unresolvedAlias = $localTypeAliases[$aliasName];
            $resolvedAliasType = $unresolvedAlias->resolve($this->typeNodeResolver);
        } catch (\PHPStan\Type\CircularTypeAliasDefinitionException $e) {
            $resolvedAliasType = new \PHPStan\Type\ErrorType();
        }
        $this->resolvedLocalTypeAliases[$aliasNameInClassScope] = $resolvedAliasType;
        unset($this->inProcess[$aliasNameInClassScope]);
        return $resolvedAliasType;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    private function resolveGlobalTypeAlias(string $aliasName, \PHPStan\Analyser\NameScope $nameScope)
    {
        if (!\array_key_exists($aliasName, $this->globalTypeAliases)) {
            return null;
        }
        if (\array_key_exists($aliasName, $this->resolvedGlobalTypeAliases)) {
            return $this->resolvedGlobalTypeAliases[$aliasName];
        }
        if ($this->reflectionProvider->hasClass($nameScope->resolveStringName($aliasName))) {
            throw new \PHPStan\ShouldNotHappenException(\sprintf('Type alias %s already exists as a class.', $aliasName));
        }
        if (\array_key_exists($aliasName, $this->inProcess)) {
            throw new \PHPStan\ShouldNotHappenException(\sprintf('Circular definition for type alias %s.', $aliasName));
        }
        $this->inProcess[$aliasName] = \true;
        $aliasTypeString = $this->globalTypeAliases[$aliasName];
        $aliasType = $this->typeStringResolver->resolve($aliasTypeString);
        $this->resolvedGlobalTypeAliases[$aliasName] = $aliasType;
        unset($this->inProcess[$aliasName]);
        return $aliasType;
    }
}
