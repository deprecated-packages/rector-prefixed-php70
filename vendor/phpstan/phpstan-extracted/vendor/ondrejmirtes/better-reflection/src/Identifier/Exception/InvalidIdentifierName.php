<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Identifier\Exception;

use InvalidArgumentException;
use function sprintf;
class InvalidIdentifierName extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function fromInvalidName(string $name)
    {
        return new self(\sprintf('Invalid identifier name "%s"', $name));
    }
}
