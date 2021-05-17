<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\Exception;

use LogicException;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use function sprintf;
final class ClassAlreadyLoaded extends \LogicException
{
    /**
     * @return $this
     */
    public static function fromReflectionClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $reflectionClass)
    {
        return new self(\sprintf('Class %s has already been loaded into memory so cannot be modified', $reflectionClass->getName()));
    }
}