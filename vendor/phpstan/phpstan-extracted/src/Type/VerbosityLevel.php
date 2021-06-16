<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
class VerbosityLevel
{
    const TYPE_ONLY = 1;
    const VALUE = 2;
    const PRECISE = 3;
    const CACHE = 4;
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
    /** @api
     * @return $this */
    public static function typeOnly()
    {
        return self::create(self::TYPE_ONLY);
    }
    /** @api
     * @return $this */
    public static function value()
    {
        return self::create(self::VALUE);
    }
    /** @api
     * @return $this */
    public static function precise()
    {
        return self::create(self::PRECISE);
    }
    /** @api
     * @return $this */
    public static function cache()
    {
        return self::create(self::CACHE);
    }
    /** @api
     * @return $this
     * @param \PHPStan\Type\Type|null $acceptedType */
    public static function getRecommendedLevelByType(\PHPStan\Type\Type $acceptingType, $acceptedType = null)
    {
        $moreVerboseCallback = static function (\PHPStan\Type\Type $type, callable $traverse) use(&$moreVerbose) : Type {
            if ($type->isCallable()->yes()) {
                $moreVerbose = \true;
                return $type;
            }
            if ($type instanceof \PHPStan\Type\ConstantType && !$type instanceof \PHPStan\Type\NullType) {
                $moreVerbose = \true;
                return $type;
            }
            if ($type instanceof \PHPStan\Type\Accessory\AccessoryNumericStringType) {
                $moreVerbose = \true;
                return $type;
            }
            if ($type instanceof \PHPStan\Type\Accessory\NonEmptyArrayType) {
                $moreVerbose = \true;
                return $type;
            }
            if ($type instanceof \PHPStan\Type\IntegerRangeType) {
                $moreVerbose = \true;
                return $type;
            }
            return $traverse($type);
        };
        /** @var bool $moreVerbose */
        $moreVerbose = \false;
        \PHPStan\Type\TypeTraverser::map($acceptingType, $moreVerboseCallback);
        if ($moreVerbose) {
            return self::value();
        }
        if ($acceptedType === null) {
            return self::typeOnly();
        }
        $containsInvariantTemplateType = \false;
        \PHPStan\Type\TypeTraverser::map($acceptingType, static function (\PHPStan\Type\Type $type, callable $traverse) use(&$containsInvariantTemplateType) : Type {
            if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
                $reflection = $type->getClassReflection();
                if ($reflection !== null) {
                    $templateTypeMap = $reflection->getTemplateTypeMap();
                    foreach ($templateTypeMap->getTypes() as $templateType) {
                        if (!$templateType instanceof \PHPStan\Type\Generic\TemplateType) {
                            continue;
                        }
                        if (!$templateType->getVariance()->invariant()) {
                            continue;
                        }
                        $containsInvariantTemplateType = \true;
                        return $type;
                    }
                }
            }
            return $traverse($type);
        });
        if (!$containsInvariantTemplateType) {
            return self::typeOnly();
        }
        /** @var bool $moreVerbose */
        $moreVerbose = \false;
        \PHPStan\Type\TypeTraverser::map($acceptedType, $moreVerboseCallback);
        return $moreVerbose ? self::value() : self::typeOnly();
    }
    /**
     * @param callable(): string $typeOnlyCallback
     * @param callable(): string $valueCallback
     * @param callable(): string|null $preciseCallback
     * @param callable(): string|null $cacheCallback
     * @return string
     */
    public function handle(callable $typeOnlyCallback, callable $valueCallback, $preciseCallback = null, $cacheCallback = null) : string
    {
        if ($this->value === self::TYPE_ONLY) {
            return $typeOnlyCallback();
        }
        if ($this->value === self::VALUE) {
            return $valueCallback();
        }
        if ($this->value === self::PRECISE) {
            if ($preciseCallback !== null) {
                return $preciseCallback();
            }
            return $valueCallback();
        }
        if ($this->value === self::CACHE) {
            if ($cacheCallback !== null) {
                return $cacheCallback();
            }
            if ($preciseCallback !== null) {
                return $preciseCallback();
            }
            return $valueCallback();
        }
        throw new \PHPStan\ShouldNotHappenException();
    }
}
