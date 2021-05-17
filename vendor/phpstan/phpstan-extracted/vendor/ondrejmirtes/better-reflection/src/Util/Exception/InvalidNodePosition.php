<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Exception;

use InvalidArgumentException;
use function sprintf;
class InvalidNodePosition extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function fromPosition(int $position)
    {
        return new self(\sprintf('Invalid position %d', $position));
    }
}
