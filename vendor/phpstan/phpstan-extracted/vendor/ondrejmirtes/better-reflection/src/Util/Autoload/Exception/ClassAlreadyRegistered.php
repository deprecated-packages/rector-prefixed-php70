<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\Exception;

use LogicException;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use function sprintf;
final class ClassAlreadyRegistered extends \LogicException
{
    /**
     * @return $this
     */
    public static function fromReflectionClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $reflectionClass)
    {
        return new self(\sprintf('Class %s already registered', $reflectionClass->getName()));
    }
}
