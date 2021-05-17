<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\Exception;

use LogicException;
use function sprintf;
final class FailedToLoadClass extends \LogicException
{
    /**
     * @return $this
     */
    public static function fromClassName(string $className)
    {
        return new self(\sprintf('Unable to load class %s', $className));
    }
}
