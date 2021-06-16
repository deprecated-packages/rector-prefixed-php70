<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

/** @api */
class PassedByReference
{
    const NO = 1;
    const READS_ARGUMENT = 2;
    const CREATES_NEW_VARIABLE = 3;
    /** @var self[] */
    private static $registry = [];
    /** @var int */
    private $value;
    private function __construct(int $value)
    {
        $this->value = $value;
    }
    /**
     * @return $this
     */
    private static function create(int $value)
    {
        if (!\array_key_exists($value, self::$registry)) {
            self::$registry[$value] = new self($value);
        }
        return self::$registry[$value];
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
    public static function createCreatesNewVariable()
    {
        return self::create(self::CREATES_NEW_VARIABLE);
    }
    /**
     * @return $this
     */
    public static function createReadsArgument()
    {
        return self::create(self::READS_ARGUMENT);
    }
    public function no() : bool
    {
        return $this->value === self::NO;
    }
    public function yes() : bool
    {
        return !$this->no();
    }
    /**
     * @param $this $other
     */
    public function equals($other) : bool
    {
        return $this->value === $other->value;
    }
    public function createsNewVariable() : bool
    {
        return $this->value === self::CREATES_NEW_VARIABLE;
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function combine($other)
    {
        if ($this->value > $other->value) {
            return $this;
        } elseif ($this->value < $other->value) {
            return $other;
        }
        return $this;
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['value']);
    }
}
