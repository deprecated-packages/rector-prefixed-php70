<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use ReflectionMethod;
final class ClassMethodReflectionFactory
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
     * @return \ReflectionMethod|null
     */
    public function createFromPHPStanTypeAndMethodName(\PHPStan\Type\Type $type, string $methodName)
    {
        if ($type instanceof \PHPStan\Type\ThisType) {
            $type = $type->getStaticObjectType();
        }
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        return $this->createReflectionMethodIfExists($type, $methodName);
    }
    /**
     * @return \ReflectionMethod|null
     */
    public function createReflectionMethodIfExists(\PHPStan\Type\TypeWithClassName $typeWithClassName, string $method)
    {
        if (!$this->reflectionProvider->hasClass($typeWithClassName->getClassName())) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($typeWithClassName->getClassName());
        $reflectionClass = $classReflection->getNativeReflection();
        if (!$reflectionClass->hasMethod($method)) {
            return null;
        }
        return $reflectionClass->getMethod($method);
    }
}
