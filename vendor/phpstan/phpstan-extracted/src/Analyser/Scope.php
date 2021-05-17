<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
interface Scope extends \PHPStan\Reflection\ClassMemberAccessAnswerer
{
    public function getFile() : string;
    public function getFileDescription() : string;
    public function isDeclareStrictTypes() : bool;
    public function isInTrait() : bool;
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getTraitReflection();
    /**
     * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
     */
    public function getFunction();
    /**
     * @return string|null
     */
    public function getFunctionName();
    /**
     * @return string|null
     */
    public function getNamespace();
    /**
     * @return $this|null
     */
    public function getParentScope();
    public function hasVariableType(string $variableName) : \PHPStan\TrinaryLogic;
    public function getVariableType(string $variableName) : \PHPStan\Type\Type;
    public function canAnyVariableExist() : bool;
    /**
     * @return array<int, string>
     */
    public function getDefinedVariables() : array;
    public function hasConstant(\PhpParser\Node\Name $name) : bool;
    /**
     * @return \PHPStan\Reflection\PropertyReflection|null
     */
    public function getPropertyReflection(\PHPStan\Type\Type $typeWithProperty, string $propertyName);
    /**
     * @return \PHPStan\Reflection\MethodReflection|null
     */
    public function getMethodReflection(\PHPStan\Type\Type $typeWithMethod, string $methodName);
    public function isInAnonymousFunction() : bool;
    /**
     * @return \PHPStan\Reflection\ParametersAcceptor|null
     */
    public function getAnonymousFunctionReflection();
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getAnonymousFunctionReturnType();
    public function getType(\PhpParser\Node\Expr $node) : \PHPStan\Type\Type;
    /**
     * Gets type of an expression with no regards to phpDocs.
     * Works for function/method parameters only.
     *
     * @internal
     * @param Expr $expr
     * @return Type
     */
    public function getNativeType(\PhpParser\Node\Expr $expr) : \PHPStan\Type\Type;
    /**
     * @return $this
     */
    public function doNotTreatPhpDocTypesAsCertain();
    public function resolveName(\PhpParser\Node\Name $name) : string;
    public function resolveTypeByName(\PhpParser\Node\Name $name) : \PHPStan\Type\TypeWithClassName;
    /**
     * @param mixed $value
     */
    public function getTypeFromValue($value) : \PHPStan\Type\Type;
    public function isSpecified(\PhpParser\Node\Expr $node) : bool;
    public function isInClassExists(string $className) : bool;
    public function isInClosureBind() : bool;
    public function isParameterValueNullable(\PhpParser\Node\Param $parameter) : bool;
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|null $type
     * @param bool $isNullable
     * @param bool $isVariadic
     * @return Type
     */
    public function getFunctionType($type, bool $isNullable, bool $isVariadic) : \PHPStan\Type\Type;
    public function isInExpressionAssign(\PhpParser\Node\Expr $expr) : bool;
    /**
     * @return $this
     */
    public function filterByTruthyValue(\PhpParser\Node\Expr $expr);
    /**
     * @return $this
     */
    public function filterByFalseyValue(\PhpParser\Node\Expr $expr);
    public function isInFirstLevelStatement() : bool;
}
