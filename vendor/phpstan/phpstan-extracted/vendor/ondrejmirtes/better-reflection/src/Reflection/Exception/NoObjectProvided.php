<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use InvalidArgumentException;
class NoObjectProvided extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function create()
    {
        return new self('No object provided');
    }
}
