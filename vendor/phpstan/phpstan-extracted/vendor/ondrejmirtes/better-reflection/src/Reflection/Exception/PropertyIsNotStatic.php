<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use RuntimeException;
use function sprintf;
class PropertyIsNotStatic extends \RuntimeException
{
    /**
     * @return $this
     */
    public static function fromName(string $propertyName)
    {
        return new self(\sprintf('Property "%s" is not static', $propertyName));
    }
}
