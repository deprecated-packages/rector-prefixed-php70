<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;
class DummyParameter implements \PHPStan\Reflection\ParameterReflection
{
    /** @var string */
    private $name;
    /** @var \PHPStan\Type\Type */
    private $type;
    /** @var bool */
    private $optional;
    /** @var \PHPStan\Reflection\PassedByReference */
    private $passedByReference;
    /** @var bool */
    private $variadic;
    /** @var ?\PHPStan\Type\Type */
    private $defaultValue;
    /**
     * @param \PHPStan\Reflection\PassedByReference|null $passedByReference
     * @param \PHPStan\Type\Type|null $defaultValue
     */
    public function __construct(string $name, \PHPStan\Type\Type $type, bool $optional, $passedByReference, bool $variadic, $defaultValue)
    {
        $this->name = $name;
        $this->type = $type;
        $this->optional = $optional;
        $this->passedByReference = $passedByReference ?? \PHPStan\Reflection\PassedByReference::createNo();
        $this->variadic = $variadic;
        $this->defaultValue = $defaultValue;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function isOptional() : bool
    {
        return $this->optional;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    public function passedByReference() : \PHPStan\Reflection\PassedByReference
    {
        return $this->passedByReference;
    }
    public function isVariadic() : bool
    {
        return $this->variadic;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
