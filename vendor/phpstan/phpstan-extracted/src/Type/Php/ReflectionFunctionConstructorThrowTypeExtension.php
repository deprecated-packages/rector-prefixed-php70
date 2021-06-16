<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicStaticMethodThrowTypeExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use ReflectionFunction;
class ReflectionFunctionConstructorThrowTypeExtension implements \PHPStan\Type\DynamicStaticMethodThrowTypeExtension
{
    /** @var ReflectionProvider */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isStaticMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === '__construct' && $methodReflection->getDeclaringClass()->getName() === \ReflectionFunction::class;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromStaticMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\StaticCall $methodCall, \PHPStan\Analyser\Scope $scope)
    {
        if (\count($methodCall->args) < 1) {
            return $methodReflection->getThrowType();
        }
        $valueType = $scope->getType($methodCall->args[0]->value);
        foreach (\PHPStan\Type\TypeUtils::getConstantStrings($valueType) as $constantString) {
            if (!$this->reflectionProvider->hasFunction(new \PhpParser\Node\Name($constantString->getValue()), $scope)) {
                return $methodReflection->getThrowType();
            }
            $valueType = \PHPStan\Type\TypeCombinator::remove($valueType, $constantString);
        }
        if (!$valueType instanceof \PHPStan\Type\NeverType) {
            return $methodReflection->getThrowType();
        }
        return null;
    }
}
