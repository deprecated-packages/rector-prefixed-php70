<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception;

use UnexpectedValueException;
use function sprintf;
final class MissingInstalledJson extends \UnexpectedValueException implements \PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\Exception
{
    /**
     * @return $this
     */
    public static function inProjectPath(string $path)
    {
        return new self(\sprintf('Could not locate a "vendor/composer/installed.json" file in "%s"', $path));
    }
}
