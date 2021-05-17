<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
class XMLReaderOpenReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension, \PHPStan\Type\DynamicStaticMethodReturnTypeExtension
{
    const XML_READER_CLASS = 'XMLReader';
    public function getClass() : string
    {
        return self::XML_READER_CLASS;
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === 'open';
    }
    public function isStaticMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $this->isMethodSupported($methodReflection);
    }
    public function getTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\BooleanType();
    }
    public function getTypeFromStaticMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\StaticCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\UnionType([new \PHPStan\Type\ObjectType(self::XML_READER_CLASS), new \PHPStan\Type\Constant\ConstantBooleanType(\false)]);
    }
}
