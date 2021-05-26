<?php

declare (strict_types=1);
namespace RectorPrefix20210526\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector;

/**
 * @see https://en.wikipedia.org/wiki/Three-valued_logic
 * @see https://github.com/phpstan/phpstan-src/blob/cedc99f51f7b8d815036e983166d7cb51ab46734/src/TrinaryLogic.php
 *
 * @internal
 */
final class TrinaryLogic
{
    const YES = 1;
    const MAYBE = 0;
    const NO = -1;
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
        return self::$registry[$value] = self::$registry[$value] ?? new self($value);
    }
    /**
     * Return true if its known for sure that the value is true
     */
    public function yes() : bool
    {
        return $this->value === self::YES;
    }
    /**
     * Return true if its not known for sure whether the value is true or false
     */
    public function maybe() : bool
    {
        return $this->value === self::MAYBE;
    }
    /**
     * Return true if its known for sure that the value is false
     */
    public function no() : bool
    {
        return $this->value === self::NO;
    }
    /**
     * Return string representation of the value.
     * "Yes" when the value is true, "No" when its false, "Maybe" when its not known for sure whether its true or false.
     */
    public function describe() : string
    {
        static $labels = [self::NO => 'No', self::MAYBE => 'Maybe', self::YES => 'Yes'];
        return $labels[$this->value];
    }
}
