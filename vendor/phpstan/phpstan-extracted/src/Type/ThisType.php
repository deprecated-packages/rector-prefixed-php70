<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Reflection\ClassReflection;
/** @api */
class ThisType extends \PHPStan\Type\StaticType
{
    /**
     * @api
     * @param string|ClassReflection $classReflection
     */
    public function __construct($classReflection)
    {
        parent::__construct($classReflection);
    }
    /**
     * @param ClassReflection|string $classReflection
     * @return self
     */
    public function changeBaseClass($classReflection) : \PHPStan\Type\StaticType
    {
        return new self($classReflection);
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return \sprintf('$this(%s)', $this->getClassName());
    }
}
