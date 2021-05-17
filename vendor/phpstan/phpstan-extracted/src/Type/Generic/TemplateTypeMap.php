<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
class TemplateTypeMap
{
    /** @var TemplateTypeMap|null */
    private static $empty = null;
    /** @var array<string,\PHPStan\Type\Type> */
    private $types;
    /** @param array<string,\PHPStan\Type\Type> $types */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
    /**
     * @return $this
     */
    public static function createEmpty()
    {
        $empty = self::$empty;
        if ($empty !== null) {
            return $empty;
        }
        $empty = new self([]);
        self::$empty = $empty;
        return $empty;
    }
    public function isEmpty() : bool
    {
        return \count($this->types) === 0;
    }
    public function count() : int
    {
        return \count($this->types);
    }
    /** @return array<string,\PHPStan\Type\Type> */
    public function getTypes() : array
    {
        return $this->types;
    }
    public function hasType(string $name) : bool
    {
        return \array_key_exists($name, $this->types);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getType(string $name)
    {
        return $this->types[$name] ?? null;
    }
    /**
     * @return $this
     */
    public function unsetType(string $name)
    {
        if (!$this->hasType($name)) {
            return $this;
        }
        $types = $this->types;
        unset($types[$name]);
        if (\count($types) === 0) {
            return self::createEmpty();
        }
        return new self($types);
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function union($other)
    {
        $result = $this->types;
        foreach ($other->types as $name => $type) {
            if (isset($result[$name])) {
                $result[$name] = \PHPStan\Type\TypeCombinator::union($result[$name], $type);
            } else {
                $result[$name] = $type;
            }
        }
        return new self($result);
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function benevolentUnion($other)
    {
        $result = $this->types;
        foreach ($other->types as $name => $type) {
            if (isset($result[$name])) {
                $result[$name] = \PHPStan\Type\TypeUtils::toBenevolentUnion(\PHPStan\Type\TypeCombinator::union($result[$name], $type));
            } else {
                $result[$name] = $type;
            }
        }
        return new self($result);
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function intersect($other)
    {
        $result = $this->types;
        foreach ($other->types as $name => $type) {
            if (isset($result[$name])) {
                $result[$name] = \PHPStan\Type\TypeCombinator::intersect($result[$name], $type);
            } else {
                $result[$name] = $type;
            }
        }
        return new self($result);
    }
    /** @param callable(string,Type):Type $cb
     * @return $this */
    public function map(callable $cb)
    {
        $types = [];
        foreach ($this->types as $name => $type) {
            $types[$name] = $cb($name, $type);
        }
        return new self($types);
    }
    /**
     * @return $this
     */
    public function resolveToBounds()
    {
        return $this->map(static function (string $name, \PHPStan\Type\Type $type) : Type {
            $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveToBounds($type);
            if ($type instanceof \PHPStan\Type\MixedType && $type->isExplicitMixed()) {
                return new \PHPStan\Type\MixedType(\false);
            }
            return $type;
        });
    }
    /**
     * @param mixed[] $properties
     * @return $this
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['types']);
    }
}
