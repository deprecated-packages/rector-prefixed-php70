<?php

declare (strict_types=1);
namespace PHPStan\Rules\Properties;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
class PropertyReflectionFinder
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     * @param \PHPStan\Analyser\Scope $scope
     * @return FoundPropertyReflection[]
     */
    public function findPropertyReflectionsFromNode($propertyFetch, \PHPStan\Analyser\Scope $scope) : array
    {
        if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
            if ($propertyFetch->name instanceof \PhpParser\Node\Identifier) {
                $names = [$propertyFetch->name->name];
            } else {
                $names = \array_map(static function (\PHPStan\Type\Constant\ConstantStringType $name) : string {
                    return $name->getValue();
                }, \PHPStan\Type\TypeUtils::getConstantStrings($scope->getType($propertyFetch->name)));
            }
            $reflections = [];
            $propertyHolderType = $scope->getType($propertyFetch->var);
            foreach ($names as $name) {
                $reflection = $this->findPropertyReflection($propertyHolderType, $name, $propertyFetch->name instanceof \PhpParser\Node\Expr ? $scope->filterByTruthyValue(new \PhpParser\Node\Expr\BinaryOp\Identical($propertyFetch->name, new \PhpParser\Node\Scalar\String_($name))) : $scope);
                if ($reflection === null) {
                    continue;
                }
                $reflections[] = $reflection;
            }
            return $reflections;
        }
        if ($propertyFetch->class instanceof \PhpParser\Node\Name) {
            $propertyHolderType = $scope->resolveTypeByName($propertyFetch->class);
        } else {
            $propertyHolderType = $scope->getType($propertyFetch->class);
        }
        if ($propertyFetch->name instanceof \PhpParser\Node\VarLikeIdentifier) {
            $names = [$propertyFetch->name->name];
        } else {
            $names = \array_map(static function (\PHPStan\Type\Constant\ConstantStringType $name) : string {
                return $name->getValue();
            }, \PHPStan\Type\TypeUtils::getConstantStrings($scope->getType($propertyFetch->name)));
        }
        $reflections = [];
        foreach ($names as $name) {
            $reflection = $this->findPropertyReflection($propertyHolderType, $name, $propertyFetch->name instanceof \PhpParser\Node\Expr ? $scope->filterByTruthyValue(new \PhpParser\Node\Expr\BinaryOp\Identical($propertyFetch->name, new \PhpParser\Node\Scalar\String_($name))) : $scope);
            if ($reflection === null) {
                continue;
            }
            $reflections[] = $reflection;
        }
        return $reflections;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     * @param \PHPStan\Analyser\Scope $scope
     * @return FoundPropertyReflection|null
     */
    public function findPropertyReflectionFromNode($propertyFetch, \PHPStan\Analyser\Scope $scope)
    {
        if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
            if (!$propertyFetch->name instanceof \PhpParser\Node\Identifier) {
                return null;
            }
            $propertyHolderType = $scope->getType($propertyFetch->var);
            return $this->findPropertyReflection($propertyHolderType, $propertyFetch->name->name, $scope);
        }
        if (!$propertyFetch->name instanceof \PhpParser\Node\Identifier) {
            return null;
        }
        if ($propertyFetch->class instanceof \PhpParser\Node\Name) {
            $propertyHolderType = $scope->resolveTypeByName($propertyFetch->class);
        } else {
            $propertyHolderType = $scope->getType($propertyFetch->class);
        }
        return $this->findPropertyReflection($propertyHolderType, $propertyFetch->name->name, $scope);
    }
    /**
     * @return \PHPStan\Rules\Properties\FoundPropertyReflection|null
     */
    private function findPropertyReflection(\PHPStan\Type\Type $propertyHolderType, string $propertyName, \PHPStan\Analyser\Scope $scope)
    {
        if (!$propertyHolderType->hasProperty($propertyName)->yes()) {
            return null;
        }
        $originalProperty = $propertyHolderType->getProperty($propertyName, $scope);
        return new \PHPStan\Rules\Properties\FoundPropertyReflection($originalProperty, $scope, $propertyName, $originalProperty->getReadableType(), $originalProperty->getWritableType());
    }
}
