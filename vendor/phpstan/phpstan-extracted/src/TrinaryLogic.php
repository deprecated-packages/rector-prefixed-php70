<?php

declare (strict_types=1);
namespace PHPStan;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
/**
 * @see https://en.wikipedia.org/wiki/Three-valued_logic
 */
class TrinaryLogic
{
    const YES = 1;
    const MAYBE = 0;
    const NO = -1;
    /** @var int */
    private $value;
    /** @var self[] */
    private static $registry = [];
    private function __construct(int $value)
    {
        $this->value = $value;
    }
    /**
     * @return $this
     */
    public static function createYes()
    {
        return self::create(self::YES);
    }
    /**
     * @return $this
     */
    public static function createNo()
    {
        return self::create(self::NO);
    }
    /**
     * @return $this
     */
    public static function createMaybe()
    {
        return self::create(self::MAYBE);
    }
    /**
     * @return $this
     */
    public static function createFromBoolean(bool $value)
    {
        return self::create($value ? self::YES : self::NO);
    }
    /**
     * @return $this
     */
    private static function create(int $value)
    {
        self::$registry[$value] = self::$registry[$value] ?? new self($value);
        return self::$registry[$value];
    }
    public function yes() : bool
    {
        return $this->value === self::YES;
    }
    public function maybe() : bool
    {
        return $this->value === self::MAYBE;
    }
    public function no() : bool
    {
        return $this->value === self::NO;
    }
    public function toBooleanType() : \PHPStan\Type\BooleanType
    {
        if ($this->value === self::MAYBE) {
            return new \PHPStan\Type\BooleanType();
        }
        return new \PHPStan\Type\Constant\ConstantBooleanType($this->value === self::YES);
    }
    /**
     * @param $this ...$operands
     * @return $this
     */
    public function and(...$operands)
    {
        $operandValues = \array_column($operands, 'value');
        $operandValues[] = $this->value;
        return self::create(\min($operandValues));
    }
    /**
     * @param $this ...$operands
     * @return $this
     */
    public function or(...$operands)
    {
        $operandValues = \array_column($operands, 'value');
        $operandValues[] = $this->value;
        return self::create(\max($operandValues));
    }
    /**
     * @param $this ...$operands
     * @return $this
     */
    public static function extremeIdentity(...$operands)
    {
        $operandValues = \array_column($operands, 'value');
        $min = \min($operandValues);
        $max = \max($operandValues);
        return self::create($min === $max ? $min : self::MAYBE);
    }
    /**
     * @param $this ...$operands
     * @return $this
     */
    public static function maxMin(...$operands)
    {
        $operandValues = \array_column($operands, 'value');
        return self::create(\max($operandValues) > 0 ? \max($operandValues) : \min($operandValues));
    }
    /**
     * @return $this
     */
    public function negate()
    {
        return self::create(-$this->value);
    }
    /**
     * @param $this $other
     */
    public function equals($other) : bool
    {
        return $this === $other;
    }
    /**
     * @param $this $other
     * @return $this|null
     */
    public function compareTo($other)
    {
        if ($this->value > $other->value) {
            return $this;
        } elseif ($other->value > $this->value) {
            return $other;
        }
        return null;
    }
    public function describe() : string
    {
        static $labels = [self::NO => 'No', self::MAYBE => 'Maybe', self::YES => 'Yes'];
        return $labels[$this->value];
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        return self::create($properties['value']);
    }
}
