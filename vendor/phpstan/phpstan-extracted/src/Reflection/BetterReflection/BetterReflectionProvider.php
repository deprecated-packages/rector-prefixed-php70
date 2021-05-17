<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Identifier\Exception\InvalidIdentifierName;
use PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionFunction;
use PHPStan\BetterReflection\Reflection\Exception\NotAClassReflection;
use PHPStan\BetterReflection\Reflection\Exception\NotAnInterfaceReflection;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\Reflector\ConstantReflector;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\FunctionReflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider;
use PHPStan\File\FileHelper;
use PHPStan\File\RelativePathHelper;
use PHPStan\Php\PhpVersion;
use PHPStan\PhpDoc\StubPhpDocProvider;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\ClassNameHelper;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Constant\RuntimeConstantReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\GlobalConstantReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
class BetterReflectionProvider implements \PHPStan\Reflection\ReflectionProvider
{
    /** @var ReflectionProvider\ReflectionProviderProvider */
    private $reflectionProviderProvider;
    /** @var \PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider */
    private $classReflectionExtensionRegistryProvider;
    /** @var \PHPStan\BetterReflection\Reflector\ClassReflector */
    private $classReflector;
    /** @var \PHPStan\BetterReflection\Reflector\FunctionReflector */
    private $functionReflector;
    /** @var \PHPStan\BetterReflection\Reflector\ConstantReflector */
    private $constantReflector;
    /** @var \PHPStan\Type\FileTypeMapper */
    private $fileTypeMapper;
    /** @var PhpVersion */
    private $phpVersion;
    /** @var \PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider */
    private $nativeFunctionReflectionProvider;
    /** @var StubPhpDocProvider */
    private $stubPhpDocProvider;
    /** @var \PHPStan\Reflection\FunctionReflectionFactory */
    private $functionReflectionFactory;
    /** @var RelativePathHelper */
    private $relativePathHelper;
    /** @var AnonymousClassNameHelper */
    private $anonymousClassNameHelper;
    /** @var \PhpParser\PrettyPrinter\Standard */
    private $printer;
    /** @var \PHPStan\File\FileHelper */
    private $fileHelper;
    /** @var PhpStormStubsSourceStubber */
    private $phpstormStubsSourceStubber;
    /** @var \PHPStan\Reflection\FunctionReflection[] */
    private $functionReflections = [];
    /** @var \PHPStan\Reflection\ClassReflection[] */
    private $classReflections = [];
    /** @var \PHPStan\Reflection\ClassReflection[] */
    private static $anonymousClasses = [];
    public function __construct(\PHPStan\Reflection\ReflectionProvider\ReflectionProviderProvider $reflectionProviderProvider, \PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider $classReflectionExtensionRegistryProvider, \PHPStan\BetterReflection\Reflector\ClassReflector $classReflector, \PHPStan\Type\FileTypeMapper $fileTypeMapper, \PHPStan\Php\PhpVersion $phpVersion, \PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider $nativeFunctionReflectionProvider, \PHPStan\PhpDoc\StubPhpDocProvider $stubPhpDocProvider, \PHPStan\Reflection\FunctionReflectionFactory $functionReflectionFactory, \PHPStan\File\RelativePathHelper $relativePathHelper, \PHPStan\Broker\AnonymousClassNameHelper $anonymousClassNameHelper, \PhpParser\PrettyPrinter\Standard $printer, \PHPStan\File\FileHelper $fileHelper, \PHPStan\BetterReflection\Reflector\FunctionReflector $functionReflector, \PHPStan\BetterReflection\Reflector\ConstantReflector $constantReflector, \PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber $phpstormStubsSourceStubber)
    {
        $this->reflectionProviderProvider = $reflectionProviderProvider;
        $this->classReflectionExtensionRegistryProvider = $classReflectionExtensionRegistryProvider;
        $this->classReflector = $classReflector;
        $this->fileTypeMapper = $fileTypeMapper;
        $this->phpVersion = $phpVersion;
        $this->nativeFunctionReflectionProvider = $nativeFunctionReflectionProvider;
        $this->stubPhpDocProvider = $stubPhpDocProvider;
        $this->functionReflectionFactory = $functionReflectionFactory;
        $this->relativePathHelper = $relativePathHelper;
        $this->anonymousClassNameHelper = $anonymousClassNameHelper;
        $this->printer = $printer;
        $this->fileHelper = $fileHelper;
        $this->functionReflector = $functionReflector;
        $this->constantReflector = $constantReflector;
        $this->phpstormStubsSourceStubber = $phpstormStubsSourceStubber;
    }
    public function hasClass(string $className) : bool
    {
        if (isset(self::$anonymousClasses[$className])) {
            return \true;
        }
        if (!\PHPStan\Reflection\ClassNameHelper::isValidClassName($className)) {
            return \false;
        }
        try {
            $this->classReflector->reflect($className);
            return \true;
        } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $e) {
            return \false;
        } catch (\PHPStan\BetterReflection\Identifier\Exception\InvalidIdentifierName $e) {
            return \false;
        }
    }
    public function getClass(string $className) : \PHPStan\Reflection\ClassReflection
    {
        if (isset(self::$anonymousClasses[$className])) {
            return self::$anonymousClasses[$className];
        }
        try {
            $reflectionClass = $this->classReflector->reflect($className);
        } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $e) {
            throw new \PHPStan\Broker\ClassNotFoundException($className);
        }
        $reflectionClassName = \strtolower($reflectionClass->getName());
        if (\array_key_exists($reflectionClassName, $this->classReflections)) {
            return $this->classReflections[$reflectionClassName];
        }
        $classReflection = new \PHPStan\Reflection\ClassReflection($this->reflectionProviderProvider->getReflectionProvider(), $this->fileTypeMapper, $this->phpVersion, $this->classReflectionExtensionRegistryProvider->getRegistry()->getPropertiesClassReflectionExtensions(), $this->classReflectionExtensionRegistryProvider->getRegistry()->getMethodsClassReflectionExtensions(), $reflectionClass->getName(), new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($reflectionClass), null, null, $this->stubPhpDocProvider->findClassPhpDoc($reflectionClass->getName()));
        $this->classReflections[$reflectionClassName] = $classReflection;
        return $classReflection;
    }
    public function getClassName(string $className) : string
    {
        if (!$this->hasClass($className)) {
            throw new \PHPStan\Broker\ClassNotFoundException($className);
        }
        if (isset(self::$anonymousClasses[$className])) {
            return self::$anonymousClasses[$className]->getDisplayName();
        }
        $reflectionClass = $this->classReflector->reflect($className);
        return $reflectionClass->getName();
    }
    public function supportsAnonymousClasses() : bool
    {
        return \true;
    }
    public function getAnonymousClassReflection(\PhpParser\Node\Stmt\Class_ $classNode, \PHPStan\Analyser\Scope $scope) : \PHPStan\Reflection\ClassReflection
    {
        if (isset($classNode->namespacedName)) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        if (!$scope->isInTrait()) {
            $scopeFile = $scope->getFile();
        } else {
            $scopeFile = $scope->getTraitReflection()->getFileName();
            if ($scopeFile === \false) {
                $scopeFile = $scope->getFile();
            }
        }
        $filename = $this->fileHelper->normalizePath($this->relativePathHelper->getRelativePath($scopeFile), '/');
        $className = $this->anonymousClassNameHelper->getAnonymousClassName($classNode, $scopeFile);
        $classNode->name = new \PhpParser\Node\Identifier($className);
        $classNode->setAttribute('anonymousClass', \true);
        if (isset(self::$anonymousClasses[$className])) {
            return self::$anonymousClasses[$className];
        }
        $reflectionClass = \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromNode($this->classReflector, $classNode, new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource($this->printer->prettyPrint([$classNode]), $scopeFile), null);
        self::$anonymousClasses[$className] = new \PHPStan\Reflection\ClassReflection($this->reflectionProviderProvider->getReflectionProvider(), $this->fileTypeMapper, $this->phpVersion, $this->classReflectionExtensionRegistryProvider->getRegistry()->getPropertiesClassReflectionExtensions(), $this->classReflectionExtensionRegistryProvider->getRegistry()->getMethodsClassReflectionExtensions(), \sprintf('class@anonymous/%s:%s', $filename, $classNode->getLine()), new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($reflectionClass), $scopeFile, null, $this->stubPhpDocProvider->findClassPhpDoc($className));
        $this->classReflections[$className] = self::$anonymousClasses[$className];
        return self::$anonymousClasses[$className];
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasFunction(\PhpParser\Node\Name $nameNode, $scope) : bool
    {
        return $this->resolveFunctionName($nameNode, $scope) !== null;
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getFunction(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\FunctionReflection
    {
        $functionName = $this->resolveFunctionName($nameNode, $scope);
        if ($functionName === null) {
            throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
        }
        $lowerCasedFunctionName = \strtolower($functionName);
        if (isset($this->functionReflections[$lowerCasedFunctionName])) {
            return $this->functionReflections[$lowerCasedFunctionName];
        }
        $nativeFunctionReflection = $this->nativeFunctionReflectionProvider->findFunctionReflection($lowerCasedFunctionName);
        if ($nativeFunctionReflection !== null) {
            $this->functionReflections[$lowerCasedFunctionName] = $nativeFunctionReflection;
            return $nativeFunctionReflection;
        }
        $this->functionReflections[$lowerCasedFunctionName] = $this->getCustomFunction($functionName);
        return $this->functionReflections[$lowerCasedFunctionName];
    }
    private function getCustomFunction(string $functionName) : \PHPStan\Reflection\Php\PhpFunctionReflection
    {
        $reflectionFunction = new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionFunction($this->functionReflector->reflect($functionName));
        $templateTypeMap = \PHPStan\Type\Generic\TemplateTypeMap::createEmpty();
        $phpDocParameterTags = [];
        $phpDocReturnTag = null;
        $phpDocThrowsTag = null;
        $deprecatedTag = null;
        $isDeprecated = \false;
        $isInternal = \false;
        $isFinal = \false;
        $isPure = null;
        $resolvedPhpDoc = $this->stubPhpDocProvider->findFunctionPhpDoc($reflectionFunction->getName());
        if ($resolvedPhpDoc === null && $reflectionFunction->getFileName() !== \false && $reflectionFunction->getDocComment() !== \false) {
            $fileName = $reflectionFunction->getFileName();
            $docComment = $reflectionFunction->getDocComment();
            $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, null, null, $reflectionFunction->getName(), $docComment);
        }
        if ($resolvedPhpDoc !== null) {
            $templateTypeMap = $resolvedPhpDoc->getTemplateTypeMap();
            $phpDocParameterTags = $resolvedPhpDoc->getParamTags();
            $phpDocReturnTag = $resolvedPhpDoc->getReturnTag();
            $phpDocThrowsTag = $resolvedPhpDoc->getThrowsTag();
            $deprecatedTag = $resolvedPhpDoc->getDeprecatedTag();
            $isDeprecated = $resolvedPhpDoc->isDeprecated();
            $isInternal = $resolvedPhpDoc->isInternal();
            $isFinal = $resolvedPhpDoc->isFinal();
            $isPure = $resolvedPhpDoc->isPure();
        }
        return $this->functionReflectionFactory->create($reflectionFunction, $templateTypeMap, \array_map(static function (\PHPStan\PhpDoc\Tag\ParamTag $paramTag) : Type {
            return $paramTag->getType();
        }, $phpDocParameterTags), $phpDocReturnTag !== null ? $phpDocReturnTag->getType() : null, $phpDocThrowsTag !== null ? $phpDocThrowsTag->getType() : null, $deprecatedTag !== null ? $deprecatedTag->getMessage() : null, $isDeprecated, $isInternal, $isFinal, $reflectionFunction->getFileName(), $isPure);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveFunctionName(\PhpParser\Node\Name $nameNode, $scope)
    {
        return $this->resolveName($nameNode, function (string $name) : bool {
            try {
                $this->functionReflector->reflect($name);
                return \true;
            } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $e) {
                // pass
            } catch (\PHPStan\BetterReflection\Identifier\Exception\InvalidIdentifierName $e) {
                // pass
            }
            if ($this->nativeFunctionReflectionProvider->findFunctionReflection($name) !== null) {
                return $this->phpstormStubsSourceStubber->isPresentFunction($name) !== \false;
            }
            return \false;
        }, $scope);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasConstant(\PhpParser\Node\Name $nameNode, $scope) : bool
    {
        return $this->resolveConstantName($nameNode, $scope) !== null;
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getConstant(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\GlobalConstantReflection
    {
        $constantName = $this->resolveConstantName($nameNode, $scope);
        if ($constantName === null) {
            throw new \PHPStan\Broker\ConstantNotFoundException((string) $nameNode);
        }
        $constantReflection = $this->constantReflector->reflect($constantName);
        try {
            $constantValue = $constantReflection->getValue();
            $constantValueType = \PHPStan\Type\ConstantTypeHelper::getTypeFromValue($constantValue);
            $fileName = $constantReflection->getFileName();
        } catch (\PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode $e) {
            $constantValueType = new \PHPStan\Type\MixedType();
            $fileName = null;
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAClassReflection $e) {
            $constantValueType = new \PHPStan\Type\MixedType();
            $fileName = null;
        } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAnInterfaceReflection $e) {
            $constantValueType = new \PHPStan\Type\MixedType();
            $fileName = null;
        }
        return new \PHPStan\Reflection\Constant\RuntimeConstantReflection($constantName, $constantValueType, $fileName);
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveConstantName(\PhpParser\Node\Name $nameNode, $scope)
    {
        return $this->resolveName($nameNode, function (string $name) : bool {
            try {
                $this->constantReflector->reflect($name);
                return \true;
            } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $e) {
                // pass
            } catch (\PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode $e) {
                // pass
            } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAClassReflection $e) {
                // pass
            } catch (\PHPStan\BetterReflection\Reflection\Exception\NotAnInterfaceReflection $e) {
                // pass
            }
            return \false;
        }, $scope);
    }
    /**
     * @param \PhpParser\Node\Name $nameNode
     * @param \Closure(string $name): bool $existsCallback
     * @param Scope|null $scope
     * @return string|null
     */
    private function resolveName(\PhpParser\Node\Name $nameNode, \Closure $existsCallback, $scope)
    {
        $name = (string) $nameNode;
        if ($scope !== null && $scope->getNamespace() !== null && !$nameNode->isFullyQualified()) {
            $namespacedName = \sprintf('%s\\%s', $scope->getNamespace(), $name);
            if ($existsCallback($namespacedName)) {
                return $namespacedName;
            }
        }
        if ($existsCallback($name)) {
            return $name;
        }
        return null;
    }
}
