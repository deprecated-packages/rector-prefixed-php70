<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use LogicException;
use PHPStan\BetterReflection\Reflection\ReflectionNamedType as BetterReflectionNamedType;
use PHPStan\BetterReflection\Reflection\ReflectionType as BetterReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionUnionType as BetterReflectionUnionType;
use ReflectionType as CoreReflectionType;
use function get_class;
use function sprintf;
class ReflectionType
{
    private function __construct()
    {
    }
    /**
     * @param \PHPStan\BetterReflection\Reflection\ReflectionType|null $betterReflectionType
     * @return \ReflectionType|null
     */
    public static function fromReturnTypeOrNull($betterReflectionType)
    {
        if ($betterReflectionType === null) {
            return null;
        }
        if ($betterReflectionType instanceof \PHPStan\BetterReflection\Reflection\ReflectionNamedType) {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType($betterReflectionType);
        }
        if ($betterReflectionType instanceof \PHPStan\BetterReflection\Reflection\ReflectionUnionType) {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType($betterReflectionType);
        }
        throw new \LogicException(\sprintf('%s is not supported.', \get_class($betterReflectionType)));
    }
}
