<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Identifier;

use InvalidArgumentException;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use function array_key_exists;
use function sprintf;
class IdentifierType
{
    const IDENTIFIER_CLASS = \PHPStan\BetterReflection\Reflection\ReflectionClass::class;
    const IDENTIFIER_FUNCTION = \PHPStan\BetterReflection\Reflection\ReflectionFunction::class;
    const IDENTIFIER_CONSTANT = \PHPStan\BetterReflection\Reflection\ReflectionConstant::class;
    const VALID_TYPES = [self::IDENTIFIER_CLASS => null, self::IDENTIFIER_FUNCTION => null, self::IDENTIFIER_CONSTANT => null];
    /** @var string */
    private $name;
    public function __construct(string $type = self::IDENTIFIER_CLASS)
    {
        if (!\array_key_exists($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException(\sprintf('%s is not a valid identifier type', $type));
        }
        $this->name = $type;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function isClass() : bool
    {
        return $this->name === self::IDENTIFIER_CLASS;
    }
    public function isFunction() : bool
    {
        return $this->name === self::IDENTIFIER_FUNCTION;
    }
    public function isConstant() : bool
    {
        return $this->name === self::IDENTIFIER_CONSTANT;
    }
    /**
     * Check to see if a reflector is of a valid type specified by this identifier.
     */
    public function isMatchingReflector(\PHPStan\BetterReflection\Reflection\Reflection $reflector) : bool
    {
        if ($this->name === self::IDENTIFIER_CLASS) {
            return $reflector instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass;
        }
        if ($this->name === self::IDENTIFIER_FUNCTION) {
            return $reflector instanceof \PHPStan\BetterReflection\Reflection\ReflectionFunction;
        }
        if ($this->name === self::IDENTIFIER_CONSTANT) {
            return $reflector instanceof \PHPStan\BetterReflection\Reflection\ReflectionConstant;
        }
        return \false;
    }
}
