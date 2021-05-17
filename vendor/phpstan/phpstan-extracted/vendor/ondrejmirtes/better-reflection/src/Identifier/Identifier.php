<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Identifier;

use PHPStan\BetterReflection\Identifier\Exception\InvalidIdentifierName;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract;
use function ltrim;
use function preg_match;
use function strpos;
class Identifier
{
    const WILDCARD = '*';
    const VALID_NAME_REGEXP = '/([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)(\\\\[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)*/';
    /** @var string */
    private $name;
    /** @var IdentifierType */
    private $type;
    /**
     * @throws InvalidIdentifierName
     */
    public function __construct(string $name, \PHPStan\BetterReflection\Identifier\IdentifierType $type)
    {
        $this->type = $type;
        if ($name === self::WILDCARD || $name === \PHPStan\BetterReflection\Reflection\ReflectionFunctionAbstract::CLOSURE_NAME || \strpos($name, \PHPStan\BetterReflection\Reflection\ReflectionClass::ANONYMOUS_CLASS_NAME_PREFIX) === 0) {
            $this->name = $name;
            return;
        }
        $name = \ltrim($name, '\\');
        if (!\preg_match(self::VALID_NAME_REGEXP, $name)) {
            throw \PHPStan\BetterReflection\Identifier\Exception\InvalidIdentifierName::fromInvalidName($name);
        }
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : \PHPStan\BetterReflection\Identifier\IdentifierType
    {
        return $this->type;
    }
    public function isClass() : bool
    {
        return $this->type->isClass();
    }
    public function isFunction() : bool
    {
        return $this->type->isFunction();
    }
    public function isConstant() : bool
    {
        return $this->type->isConstant();
    }
}
