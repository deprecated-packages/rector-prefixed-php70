<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

/** @api */
class TypeSpecifierContext
{
    const CONTEXT_TRUE = 0b1;
    const CONTEXT_TRUTHY_BUT_NOT_TRUE = 0b10;
    const CONTEXT_TRUTHY = self::CONTEXT_TRUE | self::CONTEXT_TRUTHY_BUT_NOT_TRUE;
    const CONTEXT_FALSE = 0b100;
    const CONTEXT_FALSEY_BUT_NOT_FALSE = 0b1000;
    const CONTEXT_FALSEY = self::CONTEXT_FALSE | self::CONTEXT_FALSEY_BUT_NOT_FALSE;
    /** @var int|null */
    private $value;
    /** @var self[] */
    private static $registry;
    /**
     * @param int|null $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }
    /**
     * @return $this
     * @param int|null $value
     */
    private static function create($value)
    {
        self::$registry[$value] = self::$registry[$value] ?? new self($value);
        return self::$registry[$value];
    }
    /**
     * @return $this
     */
    public static function createTrue()
    {
        return self::create(self::CONTEXT_TRUE);
    }
    /**
     * @return $this
     */
    public static function createTruthy()
    {
        return self::create(self::CONTEXT_TRUTHY);
    }
    /**
     * @return $this
     */
    public static function createFalse()
    {
        return self::create(self::CONTEXT_FALSE);
    }
    /**
     * @return $this
     */
    public static function createFalsey()
    {
        return self::create(self::CONTEXT_FALSEY);
    }
    /**
     * @return $this
     */
    public static function createNull()
    {
        return self::create(null);
    }
    /**
     * @return $this
     */
    public function negate()
    {
        if ($this->value === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return self::create(~$this->value);
    }
    public function true() : bool
    {
        return $this->value !== null && (bool) ($this->value & self::CONTEXT_TRUE);
    }
    public function truthy() : bool
    {
        return $this->value !== null && (bool) ($this->value & self::CONTEXT_TRUTHY);
    }
    public function false() : bool
    {
        return $this->value !== null && (bool) ($this->value & self::CONTEXT_FALSE);
    }
    public function falsey() : bool
    {
        return $this->value !== null && (bool) ($this->value & self::CONTEXT_FALSEY);
    }
    public function null() : bool
    {
        return $this->value === null;
    }
}
