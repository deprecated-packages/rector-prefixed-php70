<?php

declare (strict_types=1);
namespace PHPStan\Reflection\ReflectionProvider;

use RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\GlobalConstantReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\ReflectionWithFilename;
class ClassBlacklistReflectionProvider implements \PHPStan\Reflection\ReflectionProvider
{
    /** @var ReflectionProvider */
    private $reflectionProvider;
    /** @var PhpStormStubsSourceStubber */
    private $phpStormStubsSourceStubber;
    /** @var string[] */
    private $patterns;
    /** @var string|null */
    private $singleReflectionInsteadOfFile;
    /**
     * @param \PHPStan\Reflection\ReflectionProvider $reflectionProvider
     * @param string[] $patterns
     * @param string|null $singleReflectionInsteadOfFile
     */
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber $phpStormStubsSourceStubber, array $patterns, $singleReflectionInsteadOfFile)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpStormStubsSourceStubber = $phpStormStubsSourceStubber;
        $this->patterns = $patterns;
        $this->singleReflectionInsteadOfFile = $singleReflectionInsteadOfFile;
    }
    public function hasClass(string $className) : bool
    {
        if ($this->isClassBlacklisted($className)) {
            return \false;
        }
        $has = $this->reflectionProvider->hasClass($className);
        if (!$has) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($this->singleReflectionInsteadOfFile !== null) {
            if ($classReflection->getFileName() === $this->singleReflectionInsteadOfFile) {
                return \false;
            }
        }
        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if ($this->isClassBlacklisted($parentClassName)) {
                return \false;
            }
        }
        foreach ($classReflection->getNativeReflection()->getInterfaceNames() as $interfaceName) {
            if ($this->isClassBlacklisted($interfaceName)) {
                return \false;
            }
        }
        return \true;
    }
    private function isClassBlacklisted(string $className) : bool
    {
        if ($this->phpStormStubsSourceStubber->hasClass($className)) {
            // check that userland class isn't aliased to the same name as a class from stubs
            if (!\class_exists($className, \false)) {
                return \true;
            }
            if (\in_array(\strtolower($className), ['reflectionuniontype', 'attribute'], \true)) {
                return \true;
            }
            $reflection = new \ReflectionClass($className);
            if ($reflection->getFileName() === \false) {
                return \true;
            }
        }
        foreach ($this->patterns as $pattern) {
            if (\RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($className, $pattern) !== null) {
                return \true;
            }
        }
        return \false;
    }
    public function getClass(string $className) : \PHPStan\Reflection\ClassReflection
    {
        if (!$this->hasClass($className)) {
            throw new \PHPStan\Broker\ClassNotFoundException($className);
        }
        return $this->reflectionProvider->getClass($className);
    }
    public function getClassName(string $className) : string
    {
        if (!$this->hasClass($className)) {
            throw new \PHPStan\Broker\ClassNotFoundException($className);
        }
        return $this->reflectionProvider->getClassName($className);
    }
    public function supportsAnonymousClasses() : bool
    {
        return \false;
    }
    public function getAnonymousClassReflection(\PhpParser\Node\Stmt\Class_ $classNode, \PHPStan\Analyser\Scope $scope) : \PHPStan\Reflection\ClassReflection
    {
        throw new \PHPStan\ShouldNotHappenException();
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasFunction(\PhpParser\Node\Name $nameNode, $scope) : bool
    {
        $has = $this->reflectionProvider->hasFunction($nameNode, $scope);
        if (!$has) {
            return \false;
        }
        if ($this->singleReflectionInsteadOfFile === null) {
            return \true;
        }
        $functionReflection = $this->reflectionProvider->getFunction($nameNode, $scope);
        if (!$functionReflection instanceof \PHPStan\Reflection\ReflectionWithFilename) {
            return \true;
        }
        return $functionReflection->getFileName() !== $this->singleReflectionInsteadOfFile;
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getFunction(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\FunctionReflection
    {
        return $this->reflectionProvider->getFunction($nameNode, $scope);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveFunctionName(\PhpParser\Node\Name $nameNode, $scope)
    {
        return $this->reflectionProvider->resolveFunctionName($nameNode, $scope);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasConstant(\PhpParser\Node\Name $nameNode, $scope) : bool
    {
        return $this->reflectionProvider->hasConstant($nameNode, $scope);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getConstant(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\GlobalConstantReflection
    {
        return $this->reflectionProvider->getConstant($nameNode, $scope);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveConstantName(\PhpParser\Node\Name $nameNode, $scope)
    {
        return $this->reflectionProvider->resolveConstantName($nameNode, $scope);
    }
}
