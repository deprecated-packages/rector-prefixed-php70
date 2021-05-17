<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod\Exception;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use RuntimeException;
use function sprintf;
final class SignatureCheckFailed extends \RuntimeException
{
    /**
     * @return $this
     */
    public static function fromReflectionClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $reflectionClass)
    {
        return new self(\sprintf('Failed to verify the signature of the cached file for %s', $reflectionClass->getName()));
    }
}
