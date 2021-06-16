<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
/** @api */
class TemplateTypeVariance
{
    const INVARIANT = 1;
    const COVARIANT = 2;
    const CONTRAVARIANT = 3;
    const STATIC = 4;
    /** @var self[] */
    private static $registry;
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
        self::$registry[$value] = self::$registry[$value] ?? new self($value);
        return self::$registry[$value];
    }
    /**
     * @return $this
     */
    public static function createInvariant()
    {
        return self::create(self::INVARIANT);
    }
    /**
     * @return $this
     */
    public static function createCovariant()
    {
        return self::create(self::COVARIANT);
    }
    /**
     * @return $this
     */
    public static function createContravariant()
    {
        return self::create(self::CONTRAVARIANT);
    }
    /**
     * @return $this
     */
    public static function createStatic()
    {
        return self::create(self::STATIC);
    }
    public function invariant() : bool
    {
        return $this->value === self::INVARIANT;
    }
    public function covariant() : bool
    {
        return $this->value === self::COVARIANT;
    }
    public function contravariant() : bool
    {
        return $this->value === self::CONTRAVARIANT;
    }
    public function static() : bool
    {
        return $this->value === self::STATIC;
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function compose($other)
    {
        if ($this->contravariant()) {
            if ($other->contravariant()) {
                return self::createCovariant();
            }
            if ($other->covariant()) {
                return self::createContravariant();
            }
            return self::createInvariant();
        }
        if ($this->covariant()) {
            if ($other->contravariant()) {
                return self::createCovariant();
            }
            if ($other->covariant()) {
                return self::createCovariant();
            }
            return self::createInvariant();
        }
        return $other;
    }
    public function isValidVariance(\PHPStan\Type\Type $a, \PHPStan\Type\Type $b) : \PHPStan\TrinaryLogic
    {
        if ($a instanceof \PHPStan\Type\MixedType && !$a instanceof \PHPStan\Type\Generic\TemplateType) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        if ($a instanceof \PHPStan\Type\BenevolentUnionType) {
            if (!$a->isSuperTypeOf($b)->no()) {
                return \PHPStan\TrinaryLogic::createYes();
            }
        }
        if ($b instanceof \PHPStan\Type\BenevolentUnionType) {
            if (!$b->isSuperTypeOf($a)->no()) {
                return \PHPStan\TrinaryLogic::createYes();
            }
        }
        if ($b instanceof \PHPStan\Type\MixedType && !$b instanceof \PHPStan\Type\Generic\TemplateType) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        if ($this->invariant()) {
            return \PHPStan\TrinaryLogic::createFromBoolean($a->equals($b));
        }
        if ($this->covariant()) {
            return $a->isSuperTypeOf($b);
        }
        if ($this->contravariant()) {
            return $b->isSuperTypeOf($a);
        }
        throw new \PHPStan\ShouldNotHappenException();
    }
    /**
     * @param $this $other
     */
    public function equals($other) : bool
    {
        return $other->value === $this->value;
    }
    /**
     * @param $this $other
     */
    public function validPosition($other) : bool
    {
        return $other->value === $this->value || $other->invariant() || $this->static();
    }
    public function describe() : string
    {
        switch ($this->value) {
            case self::INVARIANT:
                return 'invariant';
            case self::COVARIANT:
                return 'covariant';
            case self::CONTRAVARIANT:
                return 'contravariant';
            case self::STATIC:
                return 'static';
        }
        throw new \PHPStan\ShouldNotHappenException();
    }
    /**
     * @param array{value: int} $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['value']);
    }
}
