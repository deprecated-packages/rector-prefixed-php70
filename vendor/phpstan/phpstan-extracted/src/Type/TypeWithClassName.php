<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Reflection\ClassReflection;
interface TypeWithClassName extends \PHPStan\Type\Type
{
    public function getClassName() : string;
    /**
     * @return $this|null
     */
    public function getAncestorWithClassName(string $className);
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getClassReflection();
}
