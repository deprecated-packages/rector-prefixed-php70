<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Exception;

use InvalidArgumentException;
use function gettype;
use function sprintf;
class NotAnObject extends \InvalidArgumentException
{
    /**
     * @param bool|int|float|string|array|resource|null $nonObject
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
     * @return $this
     */
    public static function fromNonObject($nonObject)
    {
        return new self(\sprintf('Provided "%s" is not an object', \gettype($nonObject)));
    }
}
