<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use PhpParser\Node\UnionType;
use function array_map;
use function implode;
class ReflectionUnionType extends \PHPStan\BetterReflection\Reflection\ReflectionType
{
    /** @var ReflectionType[] */
    private $types;
    public function __construct(\PhpParser\Node\UnionType $type, bool $allowsNull)
    {
        parent::__construct($allowsNull);
        $this->types = \array_map(static function ($type) : ReflectionType {
            return \PHPStan\BetterReflection\Reflection\ReflectionType::createFromTypeAndReflector($type);
        }, $type->types);
    }
    /**
     * @return ReflectionType[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }
    public function __toString() : string
    {
        return \implode('|', \array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionType $type) : string {
            return (string) $type;
        }, $this->types));
    }
}
