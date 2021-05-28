<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
final class DsMapDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'RectorPrefix20210528\\Ds\\Map';
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === 'get' || $methodReflection->getName() === 'remove';
    }
    public function getTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $returnType = \PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->args, $methodReflection->getVariants())->getReturnType();
        if (\count($methodCall->args) > 1) {
            return $returnType;
        }
        if ($returnType instanceof \PHPStan\Type\UnionType) {
            $types = \array_values(\array_filter($returnType->getTypes(), static function (\PHPStan\Type\Type $type) : bool {
                if ($type instanceof \PHPStan\Type\Generic\TemplateType && $type->getName() === 'TDefault' && ($type->getScope()->equals(\PHPStan\Type\Generic\TemplateTypeScope::createWithMethod('RectorPrefix20210528\\Ds\\Map', 'get')) || $type->getScope()->equals(\PHPStan\Type\Generic\TemplateTypeScope::createWithMethod('RectorPrefix20210528\\Ds\\Map', 'remove')))) {
                    return \false;
                }
                return \true;
            }));
            if (\count($types) === 1) {
                return $types[0];
            }
            if (\count($types) === 0) {
                return $returnType;
            }
            return \PHPStan\Type\TypeCombinator::union(...$types);
        }
        return $returnType;
    }
}
