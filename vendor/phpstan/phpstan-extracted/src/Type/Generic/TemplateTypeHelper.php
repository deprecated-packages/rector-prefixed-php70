<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
class TemplateTypeHelper
{
    /**
     * Replaces template types with standin types
     */
    public static function resolveTemplateTypes(\PHPStan\Type\Type $type, \PHPStan\Type\Generic\TemplateTypeMap $standins) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) use($standins) : Type {
            if ($type instanceof \PHPStan\Type\Generic\TemplateType && !$type->isArgument()) {
                $newType = $standins->getType($type->getName());
                if ($newType === null) {
                    return $traverse($type);
                }
                if ($newType instanceof \PHPStan\Type\ErrorType) {
                    return $traverse($type->getBound());
                }
                return $newType;
            }
            return $traverse($type);
        });
    }
    public static function resolveToBounds(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if ($type instanceof \PHPStan\Type\Generic\TemplateType) {
                return $traverse($type->getBound());
            }
            return $traverse($type);
        });
    }
    /**
     * @template T of Type
     * @param T $type
     * @return T
     */
    public static function toArgument(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        /** @var T */
        return \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if ($type instanceof \PHPStan\Type\Generic\TemplateType) {
                return $traverse($type->toArgument());
            }
            return $traverse($type);
        });
    }
    public static function generalizeType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if ($type instanceof \PHPStan\Type\ConstantType && !$type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                return $type->generalize();
            }
            return $traverse($type);
        });
    }
}
