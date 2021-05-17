<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use UnexpectedValueException;
use function sprintf;
class NotAClassReflection extends \UnexpectedValueException
{
    /**
     * @return $this
     */
    public static function fromReflectionClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $class)
    {
        $type = 'interface';
        if ($class->isTrait()) {
            $type = 'trait';
        }
        return new self(\sprintf('Provided node "%s" is not class, but "%s"', $class->getName(), $type));
    }
}
