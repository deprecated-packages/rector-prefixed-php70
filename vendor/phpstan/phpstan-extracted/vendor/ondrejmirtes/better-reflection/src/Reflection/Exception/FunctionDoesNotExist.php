<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use RuntimeException;
use function sprintf;
class FunctionDoesNotExist extends \RuntimeException
{
    /**
     * @return $this
     */
    public static function fromName(string $functionName)
    {
        return new self(\sprintf('Function "%s" cannot be used as the function is not loaded', $functionName));
    }
}
