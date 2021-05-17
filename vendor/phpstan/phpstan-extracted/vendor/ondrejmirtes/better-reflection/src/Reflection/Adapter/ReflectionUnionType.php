<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use PHPStan\BetterReflection\Reflection\ReflectionUnionType as BetterReflectionType;
use ReflectionUnionType as CoreReflectionUnionType;
use function array_map;
class ReflectionUnionType extends \ReflectionUnionType
{
    /** @var BetterReflectionType */
    private $betterReflectionType;
    public function __construct(\PHPStan\BetterReflection\Reflection\ReflectionUnionType $betterReflectionType)
    {
        $this->betterReflectionType = $betterReflectionType;
    }
    public function __toString() : string
    {
        return $this->betterReflectionType->__toString();
    }
    public function allowsNull() : bool
    {
        return $this->betterReflectionType->allowsNull();
    }
    /**
     * @return \ReflectionType[]
     */
    public function getTypes() : array
    {
        return \array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionType $type) : \ReflectionType {
            return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionType::fromReturnTypeOrNull($type);
        }, $this->betterReflectionType->getTypes());
    }
}
