<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use PHPStan\BetterReflection\Reflection\Reflection;
use RuntimeException;
use function sprintf;
class ClassDoesNotExist extends \RuntimeException
{
    /**
     * @return $this
     */
    public static function forDifferentReflectionType(\PHPStan\BetterReflection\Reflection\Reflection $reflection)
    {
        return new self(\sprintf('The reflected type "%s" is not a class', $reflection->getName()));
    }
}
