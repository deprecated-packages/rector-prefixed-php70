<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionNamedType;
/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return void
     */
    public function decorate(\PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        if (!$this->reflectionProvider->hasClass('PHPUnit\\Framework\\TestCase')) {
            return;
        }
        // skip test run
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass('PHPUnit\\Framework\\TestCase');
        $reflectionClass = $classReflection->getNativeReflection();
        $reflectionMethod = $reflectionClass->getMethod(\Rector\Core\ValueObject\MethodName::SET_UP);
        if (!$reflectionMethod->hasReturnType()) {
            return;
        }
        $returnType = $reflectionMethod->getReturnType();
        $returnTypeName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string) $returnType;
        $classMethod->returnType = new \PhpParser\Node\Identifier($returnTypeName);
    }
}
