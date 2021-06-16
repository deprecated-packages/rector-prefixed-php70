<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use DateInterval;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodThrowTypeExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
class DateIntervalConstructorThrowTypeExtension implements \PHPStan\Type\DynamicStaticMethodThrowTypeExtension
{
    public function isStaticMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === '__construct' && $methodReflection->getDeclaringClass()->getName() === \DateInterval::class;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowTypeFromStaticMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\StaticCall $methodCall, \PHPStan\Analyser\Scope $scope)
    {
        if (\count($methodCall->args) === 0) {
            return $methodReflection->getThrowType();
        }
        $valueType = $scope->getType($methodCall->args[0]->value);
        $constantStrings = \PHPStan\Type\TypeUtils::getConstantStrings($valueType);
        foreach ($constantStrings as $constantString) {
            try {
                new \DateInterval($constantString->getValue());
            } catch (\Exception $e) {
                // phpcs:ignore
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
