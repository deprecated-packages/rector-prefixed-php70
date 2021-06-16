<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
/** @api */
class IntegerRangeType extends \PHPStan\Type\IntegerType implements \PHPStan\Type\CompoundType
{
    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;
    /**
     * @param int|null $min
     * @param int|null $max
     */
    public function __construct($min, $max)
    {
        // this constructor can be made private when PHP 7.2 is the minimum
        parent::__construct();
        \assert($min === null || $max === null || $min <= $max);
        \assert($min !== null || $max !== null);
        $this->min = $min;
        $this->max = $max;
    }
    /**
     * @param int|null $min
     * @param int|null $max
     */
    public static function fromInterval($min, $max, int $shift = 0) : \PHPStan\Type\Type
    {
        if ($min !== null && $max !== null) {
            if ($min > $max) {
                return new \PHPStan\Type\NeverType();
            }
            if ($min === $max) {
                return new \PHPStan\Type\Constant\ConstantIntegerType($min + $shift);
            }
        }
        if ($min === null && $max === null) {
            return new \PHPStan\Type\IntegerType();
        }
        return (new self($min, $max))->shift($shift);
    }
    /**
     * @param int|null $minA
     * @param int|null $maxA
     * @param int|null $minB
     * @param int|null $maxB
     */
    protected static function isDisjoint($minA, $maxA, $minB, $maxB, bool $touchingIsDisjoint = \true) : bool
    {
        $offset = $touchingIsDisjoint ? 0 : 1;
        return $minA !== null && $maxB !== null && $minA > $maxB + $offset || $maxA !== null && $minB !== null && $maxA + $offset < $minB;
    }
    /**
     * Return the range of integers smaller than the given value
     *
     * @param int|float $value
     * @return Type
     */
    public static function createAllSmallerThan($value) : \PHPStan\Type\Type
    {
        if (\is_int($value)) {
            return self::fromInterval(null, $value, -1);
        }
        if ($value > \PHP_INT_MAX) {
            return new \PHPStan\Type\IntegerType();
        }
        if ($value <= \PHP_INT_MIN) {
            return new \PHPStan\Type\NeverType();
        }
        return self::fromInterval(null, (int) \ceil($value), -1);
    }
    /**
     * Return the range of integers smaller than or equal to the given value
     *
     * @param int|float $value
     * @return Type
     */
    public static function createAllSmallerThanOrEqualTo($value) : \PHPStan\Type\Type
    {
        if (\is_int($value)) {
            return self::fromInterval(null, $value);
        }
        if ($value >= \PHP_INT_MAX) {
            return new \PHPStan\Type\IntegerType();
        }
        if ($value < \PHP_INT_MIN) {
            return new \PHPStan\Type\NeverType();
        }
        return self::fromInterval(null, (int) \floor($value));
    }
    /**
     * Return the range of integers greater than the given value
     *
     * @param int|float $value
     * @return Type
     */
    public static function createAllGreaterThan($value) : \PHPStan\Type\Type
    {
        if (\is_int($value)) {
            return self::fromInterval($value, null, 1);
        }
        if ($value < \PHP_INT_MIN) {
            return new \PHPStan\Type\IntegerType();
        }
        if ($value >= \PHP_INT_MAX) {
            return new \PHPStan\Type\NeverType();
        }
        return self::fromInterval((int) \floor($value), null, 1);
    }
    /**
     * Return the range of integers greater than or equal to the given value
     *
     * @param int|float $value
     * @return Type
     */
    public static function createAllGreaterThanOrEqualTo($value) : \PHPStan\Type\Type
    {
        if (\is_int($value)) {
            return self::fromInterval($value, null);
        }
        if ($value <= \PHP_INT_MIN) {
            return new \PHPStan\Type\IntegerType();
        }
        if ($value > \PHP_INT_MAX) {
            return new \PHPStan\Type\NeverType();
        }
        return self::fromInterval((int) \ceil($value), null);
    }
    /**
     * @return int|null
     */
    public function getMin()
    {
        return $this->min;
    }
    /**
     * @return int|null
     */
    public function getMax()
    {
        return $this->max;
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return \sprintf('int<%s, %s>', $this->min ?? 'min', $this->max ?? 'max');
    }
    public function shift(int $amount) : \PHPStan\Type\Type
    {
        if ($amount === 0) {
            return $this;
        }
        $min = $this->min;
        $max = $this->max;
        if ($amount < 0) {
            if ($max !== null) {
                if ($max < \PHP_INT_MIN - $amount) {
                    return new \PHPStan\Type\NeverType();
                }
                $max += $amount;
            }
            if ($min !== null) {
                $min = $min < \PHP_INT_MIN - $amount ? null : $min + $amount;
            }
        } else {
            if ($min !== null) {
                if ($min > \PHP_INT_MAX - $amount) {
                    return new \PHPStan\Type\NeverType();
                }
                $min += $amount;
            }
            if ($max !== null) {
                $max = $max > \PHP_INT_MAX - $amount ? null : $max + $amount;
            }
        }
        return self::fromInterval($min, $max);
    }
    public function accepts(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof parent) {
            return $this->isSuperTypeOf($type);
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return \PHPStan\Type\CompoundTypeHelper::accepts($type, $this, $strictTypes);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof self || $type instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            if ($type instanceof self) {
                $typeMin = $type->min;
                $typeMax = $type->max;
            } else {
                $typeMin = $type->getValue();
                $typeMax = $type->getValue();
            }
            if (self::isDisjoint($this->min, $this->max, $typeMin, $typeMax)) {
                return \PHPStan\TrinaryLogic::createNo();
            }
            if (($this->min === null || $typeMin !== null && $this->min <= $typeMin) && ($this->max === null || $typeMax !== null && $this->max >= $typeMax)) {
                return \PHPStan\TrinaryLogic::createYes();
            }
            return \PHPStan\TrinaryLogic::createMaybe();
        }
        if ($type instanceof parent) {
            return \PHPStan\TrinaryLogic::createMaybe();
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOf($this);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isSubTypeOf(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        if ($otherType instanceof parent) {
            return $otherType->isSuperTypeOf($this);
        }
        if ($otherType instanceof \PHPStan\Type\UnionType || $otherType instanceof \PHPStan\Type\IntersectionType) {
            return $otherType->isSuperTypeOf($this);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        return $this->isSubTypeOf($acceptingType);
    }
    public function equals(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof self && $this->min === $type->min && $this->max === $type->max;
    }
    public function generalize() : \PHPStan\Type\Type
    {
        return new parent();
    }
    public function isSmallerThan(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        if ($this->min === null) {
            $minIsSmaller = \PHPStan\TrinaryLogic::createYes();
        } else {
            $minIsSmaller = (new \PHPStan\Type\Constant\ConstantIntegerType($this->min))->isSmallerThan($otherType);
        }
        if ($this->max === null) {
            $maxIsSmaller = \PHPStan\TrinaryLogic::createNo();
        } else {
            $maxIsSmaller = (new \PHPStan\Type\Constant\ConstantIntegerType($this->max))->isSmallerThan($otherType);
        }
        return \PHPStan\TrinaryLogic::extremeIdentity($minIsSmaller, $maxIsSmaller);
    }
    public function isSmallerThanOrEqual(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        if ($this->min === null) {
            $minIsSmaller = \PHPStan\TrinaryLogic::createYes();
        } else {
            $minIsSmaller = (new \PHPStan\Type\Constant\ConstantIntegerType($this->min))->isSmallerThanOrEqual($otherType);
        }
        if ($this->max === null) {
            $maxIsSmaller = \PHPStan\TrinaryLogic::createNo();
        } else {
            $maxIsSmaller = (new \PHPStan\Type\Constant\ConstantIntegerType($this->max))->isSmallerThanOrEqual($otherType);
        }
        return \PHPStan\TrinaryLogic::extremeIdentity($minIsSmaller, $maxIsSmaller);
    }
    public function isGreaterThan(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        if ($this->min === null) {
            $minIsSmaller = \PHPStan\TrinaryLogic::createNo();
        } else {
            $minIsSmaller = $otherType->isSmallerThan(new \PHPStan\Type\Constant\ConstantIntegerType($this->min));
        }
        if ($this->max === null) {
            $maxIsSmaller = \PHPStan\TrinaryLogic::createYes();
        } else {
            $maxIsSmaller = $otherType->isSmallerThan(new \PHPStan\Type\Constant\ConstantIntegerType($this->max));
        }
        return \PHPStan\TrinaryLogic::extremeIdentity($minIsSmaller, $maxIsSmaller);
    }
    public function isGreaterThanOrEqual(\PHPStan\Type\Type $otherType) : \PHPStan\TrinaryLogic
    {
        if ($this->min === null) {
            $minIsSmaller = \PHPStan\TrinaryLogic::createNo();
        } else {
            $minIsSmaller = $otherType->isSmallerThanOrEqual(new \PHPStan\Type\Constant\ConstantIntegerType($this->min));
        }
        if ($this->max === null) {
            $maxIsSmaller = \PHPStan\TrinaryLogic::createYes();
        } else {
            $maxIsSmaller = $otherType->isSmallerThanOrEqual(new \PHPStan\Type\Constant\ConstantIntegerType($this->max));
        }
        return \PHPStan\TrinaryLogic::extremeIdentity($minIsSmaller, $maxIsSmaller);
    }
    public function getSmallerType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [new \PHPStan\Type\Constant\ConstantBooleanType(\true)];
        if ($this->max !== null) {
            $subtractedTypes[] = self::createAllGreaterThanOrEqualTo($this->max);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getSmallerOrEqualType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [];
        if ($this->max !== null) {
            $subtractedTypes[] = self::createAllGreaterThan($this->max);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getGreaterType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [new \PHPStan\Type\NullType(), new \PHPStan\Type\Constant\ConstantBooleanType(\false)];
        if ($this->min !== null) {
            $subtractedTypes[] = self::createAllSmallerThanOrEqualTo($this->min);
        }
        if ($this->min !== null && $this->min > 0 || $this->max !== null && $this->max < 0) {
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\true);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function getGreaterOrEqualType() : \PHPStan\Type\Type
    {
        $subtractedTypes = [];
        if ($this->min !== null) {
            $subtractedTypes[] = self::createAllSmallerThan($this->min);
        }
        if ($this->min !== null && $this->min > 0 || $this->max !== null && $this->max < 0) {
            $subtractedTypes[] = new \PHPStan\Type\NullType();
            $subtractedTypes[] = new \PHPStan\Type\Constant\ConstantBooleanType(\false);
        }
        return \PHPStan\Type\TypeCombinator::remove(new \PHPStan\Type\MixedType(), \PHPStan\Type\TypeCombinator::union(...$subtractedTypes));
    }
    public function toNumber() : \PHPStan\Type\Type
    {
        return new parent();
    }
    public function toBoolean() : \PHPStan\Type\BooleanType
    {
        $isZero = (new \PHPStan\Type\Constant\ConstantIntegerType(0))->isSuperTypeOf($this);
        if ($isZero->no()) {
            return new \PHPStan\Type\Constant\ConstantBooleanType(\true);
        }
        if ($isZero->maybe()) {
            return new \PHPStan\Type\BooleanType();
        }
        return new \PHPStan\Type\Constant\ConstantBooleanType(\false);
    }
    /**
     * Return the union with another type, but only if it can be expressed in a simpler way than using UnionType
     *
     * @param Type $otherType
     * @return Type|null
     */
    public function tryUnion(\PHPStan\Type\Type $otherType)
    {
        if ($otherType instanceof self || $otherType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            if ($otherType instanceof self) {
                $otherMin = $otherType->min;
                $otherMax = $otherType->max;
            } else {
                $otherMin = $otherType->getValue();
                $otherMax = $otherType->getValue();
            }
            if (self::isDisjoint($this->min, $this->max, $otherMin, $otherMax, \false)) {
                return null;
            }
            return self::fromInterval($this->min !== null && $otherMin !== null ? \min($this->min, $otherMin) : null, $this->max !== null && $otherMax !== null ? \max($this->max, $otherMax) : null);
        }
        if (\get_class($otherType) === parent::class) {
            return $otherType;
        }
        return null;
    }
    /**
     * Return the intersection with another type, but only if it can be expressed in a simpler way than using
     * IntersectionType
     *
     * @param Type $otherType
     * @return Type|null
     */
    public function tryIntersect(\PHPStan\Type\Type $otherType)
    {
        if ($otherType instanceof self || $otherType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            if ($otherType instanceof self) {
                $otherMin = $otherType->min;
                $otherMax = $otherType->max;
            } else {
                $otherMin = $otherType->getValue();
                $otherMax = $otherType->getValue();
            }
            if (self::isDisjoint($this->min, $this->max, $otherMin, $otherMax, \false)) {
                return new \PHPStan\Type\NeverType();
            }
            if ($this->min === null) {
                $newMin = $otherMin;
            } elseif ($otherMin === null) {
                $newMin = $this->min;
            } else {
                $newMin = \max($this->min, $otherMin);
            }
            if ($this->max === null) {
                $newMax = $otherMax;
            } elseif ($otherMax === null) {
                $newMax = $this->max;
            } else {
                $newMax = \min($this->max, $otherMax);
            }
            return self::fromInterval($newMin, $newMax);
        }
        if (\get_class($otherType) === parent::class) {
            return $this;
        }
        return null;
    }
    /**
     * Return the different with another type, or null if it cannot be represented.
     *
     * @param Type $typeToRemove
     * @return Type|null
     */
    public function tryRemove(\PHPStan\Type\Type $typeToRemove)
    {
        if (\get_class($typeToRemove) === parent::class) {
            return new \PHPStan\Type\NeverType();
        }
        if ($typeToRemove instanceof \PHPStan\Type\IntegerRangeType || $typeToRemove instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            if ($typeToRemove instanceof \PHPStan\Type\IntegerRangeType) {
                $removeMin = $typeToRemove->min;
                $removeMax = $typeToRemove->max;
            } else {
                $removeMin = $typeToRemove->getValue();
                $removeMax = $typeToRemove->getValue();
            }
            if ($this->min !== null && $removeMax !== null && $removeMax < $this->min || $this->max !== null && $removeMin !== null && $this->max < $removeMin) {
                return $this;
            }
            if ($removeMin !== null && $removeMin !== \PHP_INT_MIN) {
                $lowerPart = self::fromInterval($this->min, $removeMin - 1);
            } else {
                $lowerPart = null;
            }
            if ($removeMax !== null && $removeMax !== \PHP_INT_MAX) {
                $upperPart = self::fromInterval($removeMax + 1, $this->max);
            } else {
                $upperPart = null;
            }
            if ($lowerPart !== null && $upperPart !== null) {
                return \PHPStan\Type\TypeCombinator::union($lowerPart, $upperPart);
            }
            return $lowerPart ?? $upperPart;
        }
        return null;
    }
    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['min'], $properties['max']);
    }
}
