<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
/** @api */
interface ReflectionProvider
{
    public function hasClass(string $className) : bool;
    public function getClass(string $className) : \PHPStan\Reflection\ClassReflection;
    public function getClassName(string $className) : string;
    public function supportsAnonymousClasses() : bool;
    public function getAnonymousClassReflection(\PhpParser\Node\Stmt\Class_ $classNode, \PHPStan\Analyser\Scope $scope) : \PHPStan\Reflection\ClassReflection;
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasFunction(\PhpParser\Node\Name $nameNode, $scope) : bool;
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getFunction(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\FunctionReflection;
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveFunctionName(\PhpParser\Node\Name $nameNode, $scope);
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function hasConstant(\PhpParser\Node\Name $nameNode, $scope) : bool;
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     */
    public function getConstant(\PhpParser\Node\Name $nameNode, $scope) : \PHPStan\Reflection\GlobalConstantReflection;
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return string|null
     */
    public function resolveConstantName(\PhpParser\Node\Name $nameNode, $scope);
}
