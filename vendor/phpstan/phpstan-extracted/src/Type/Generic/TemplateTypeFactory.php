<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\PhpDoc\Tag\TemplateTag;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class TemplateTypeFactory
{
    /**
     * @param \PHPStan\Type\Type|null $bound
     */
    public static function create(\PHPStan\Type\Generic\TemplateTypeScope $scope, string $name, $bound, \PHPStan\Type\Generic\TemplateTypeVariance $variance) : \PHPStan\Type\Generic\TemplateType
    {
        $strategy = new \PHPStan\Type\Generic\TemplateTypeParameterStrategy();
        if ($bound === null) {
            return new \PHPStan\Type\Generic\TemplateMixedType($scope, $strategy, $variance, $name, new \PHPStan\Type\MixedType(\true));
        }
        $boundClass = \get_class($bound);
        if ($bound instanceof \PHPStan\Type\ObjectType && ($boundClass === \PHPStan\Type\ObjectType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateObjectType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\Generic\GenericObjectType && ($boundClass === \PHPStan\Type\Generic\GenericObjectType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateGenericObjectType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\ObjectWithoutClassType && ($boundClass === \PHPStan\Type\ObjectWithoutClassType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateObjectWithoutClassType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\StringType && ($boundClass === \PHPStan\Type\StringType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateStringType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\IntegerType && ($boundClass === \PHPStan\Type\IntegerType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateIntegerType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\MixedType && ($boundClass === \PHPStan\Type\MixedType::class || $bound instanceof \PHPStan\Type\Generic\TemplateType)) {
            return new \PHPStan\Type\Generic\TemplateMixedType($scope, $strategy, $variance, $name, $bound);
        }
        if ($bound instanceof \PHPStan\Type\UnionType) {
            if ($boundClass === \PHPStan\Type\UnionType::class || $bound instanceof \PHPStan\Type\Generic\TemplateUnionType) {
                return new \PHPStan\Type\Generic\TemplateUnionType($scope, $strategy, $variance, $name, $bound);
            }
            if ($bound instanceof \PHPStan\Type\BenevolentUnionType) {
                return new \PHPStan\Type\Generic\TemplateBenevolentUnionType($scope, $strategy, $variance, $name, $bound);
            }
        }
        return new \PHPStan\Type\Generic\TemplateMixedType($scope, $strategy, $variance, $name, new \PHPStan\Type\MixedType(\true));
    }
    public static function fromTemplateTag(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\PhpDoc\Tag\TemplateTag $tag) : \PHPStan\Type\Generic\TemplateType
    {
        return self::create($scope, $tag->getName(), $tag->getBound(), $tag->getVariance());
    }
}
