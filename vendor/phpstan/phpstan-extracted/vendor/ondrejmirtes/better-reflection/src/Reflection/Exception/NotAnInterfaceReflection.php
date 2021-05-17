<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use UnexpectedValueException;
use function sprintf;
class NotAnInterfaceReflection extends \UnexpectedValueException
{
    /**
     * @return $this
     */
    public static function fromReflectionClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $class)
    {
        $type = 'class';
        if ($class->isTrait()) {
            $type = 'trait';
        }
        return new self(\sprintf('Provided node "%s" is not interface, but "%s"', $class->getName(), $type));
    }
}
