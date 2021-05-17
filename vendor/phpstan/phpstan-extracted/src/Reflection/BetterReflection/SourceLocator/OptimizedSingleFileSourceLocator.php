<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceLocator;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
class OptimizedSingleFileSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var \PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher */
    private $fileNodesFetcher;
    /** @var string */
    private $fileName;
    /** @var \PHPStan\Reflection\BetterReflection\SourceLocator\FetchedNodesResult|null */
    private $fetchedNodesResult = null;
    public function __construct(\PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher $fileNodesFetcher, string $fileName)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
        $this->fileName = $fileName;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if ($this->fetchedNodesResult === null) {
            $this->fetchedNodesResult = $this->fileNodesFetcher->fetchNodes($this->fileName);
        }
        $nodeToReflection = new \PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection();
        if ($identifier->isClass()) {
            $classNodes = $this->fetchedNodesResult->getClassNodes();
            $className = \strtolower($identifier->getName());
            if (!\array_key_exists($className, $classNodes)) {
                return null;
            }
            foreach ($classNodes[$className] as $classNode) {
                $classReflection = $nodeToReflection->__invoke($reflector, $classNode->getNode(), $this->fetchedNodesResult->getLocatedSource(), $classNode->getNamespace());
                if (!$classReflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass) {
                    throw new \PHPStan\ShouldNotHappenException();
                }
                return $classReflection;
            }
        }
        if ($identifier->isFunction()) {
            $functionNodes = $this->fetchedNodesResult->getFunctionNodes();
            $functionName = \strtolower($identifier->getName());
            if (!\array_key_exists($functionName, $functionNodes)) {
                return null;
            }
            $functionReflection = $nodeToReflection->__invoke($reflector, $functionNodes[$functionName]->getNode(), $this->fetchedNodesResult->getLocatedSource(), $functionNodes[$functionName]->getNamespace());
            if (!$functionReflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionFunction) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            return $functionReflection;
        }
        if ($identifier->isConstant()) {
            $constantNodes = $this->fetchedNodesResult->getConstantNodes();
            foreach ($constantNodes as $stmtConst) {
                if ($stmtConst->getNode() instanceof \PhpParser\Node\Expr\FuncCall) {
                    $constantReflection = $nodeToReflection->__invoke($reflector, $stmtConst->getNode(), $this->fetchedNodesResult->getLocatedSource(), $stmtConst->getNamespace());
                    if ($constantReflection === null) {
                        continue;
                    }
                    if (!$constantReflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionConstant) {
                        throw new \PHPStan\ShouldNotHappenException();
                    }
                    if ($constantReflection->getName() !== $identifier->getName()) {
                        continue;
                    }
                    return $constantReflection;
                }
                foreach (\array_keys($stmtConst->getNode()->consts) as $i) {
                    $constantReflection = $nodeToReflection->__invoke($reflector, $stmtConst->getNode(), $this->fetchedNodesResult->getLocatedSource(), $stmtConst->getNamespace(), $i);
                    if ($constantReflection === null) {
                        continue;
                    }
                    if (!$constantReflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionConstant) {
                        throw new \PHPStan\ShouldNotHappenException();
                    }
                    if ($constantReflection->getName() !== $identifier->getName()) {
                        continue;
                    }
                    return $constantReflection;
                }
            }
            return null;
        }
        throw new \PHPStan\ShouldNotHappenException();
    }
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return [];
    }
}
