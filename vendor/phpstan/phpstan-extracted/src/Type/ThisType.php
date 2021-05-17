<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Reflection\ClassReflection;
class ThisType extends \PHPStan\Type\StaticType
{
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
