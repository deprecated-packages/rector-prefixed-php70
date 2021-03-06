<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
class DefineConstantTypeSpecifyingExtension implements \PHPStan\Type\FunctionTypeSpecifyingExtension, \PHPStan\Analyser\TypeSpecifierAwareExtension
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
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\TypeSpecifierContext $context) : bool
    {
        return $functionReflection->getName() === 'define' && $context->null() && \count($node->args) >= 2;
    }
    public function specifyTypes(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        $constantName = $scope->getType($node->args[0]->value);
        if (!$constantName instanceof \PHPStan\Type\Constant\ConstantStringType || $constantName->getValue() === '') {
            return new \PHPStan\Analyser\SpecifiedTypes([], []);
        }
        return $this->typeSpecifier->create(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name\FullyQualified($constantName->getValue())), $scope->getType($node->args[1]->value), \PHPStan\Analyser\TypeSpecifierContext::createTruthy(), \false, $scope);
    }
}
