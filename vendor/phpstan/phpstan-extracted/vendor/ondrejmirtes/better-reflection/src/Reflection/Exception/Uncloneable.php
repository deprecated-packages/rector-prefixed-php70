<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use LogicException;
class Uncloneable extends \LogicException
{
    /**
     * @return $this
     */
    public static function fromClass(string $className)
    {
        return new self('Trying to clone an uncloneable object of class ' . $className);
    }
}
