<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\ObjectType;
class ReflectionClassIsSubclassOfTypeSpecifyingExtension implements \PHPStan\Type\MethodTypeSpecifyingExtension, \PHPStan\Analyser\TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;
    /**
     * @return void
     */
    public function setTypeSpecifier(\PHPStan\Analyser\TypeSpecifier $typeSpecifier)
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function getClass() : string
    {
        return \ReflectionClass::class;
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $node, \PHPStan\Analyser\TypeSpecifierContext $context) : bool
    {
        return $methodReflection->getName() === 'isSubclassOf' && isset($node->args[0]) && $context->true();
    }
    public function specifyTypes(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $node, \PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        $valueType = $scope->getType($node->args[0]->value);
        if (!$valueType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return new \PHPStan\Analyser\SpecifiedTypes([], []);
        }
        return $this->typeSpecifier->create($node->var, new \PHPStan\Type\Generic\GenericObjectType(\ReflectionClass::class, [new \PHPStan\Type\ObjectType($valueType->getValue())]), $context, \false, $scope);
    }
}
