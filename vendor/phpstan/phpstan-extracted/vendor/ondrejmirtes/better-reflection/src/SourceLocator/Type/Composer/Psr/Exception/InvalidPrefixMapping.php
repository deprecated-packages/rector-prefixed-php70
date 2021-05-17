<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Exception;

use InvalidArgumentException;
use function sprintf;
class InvalidPrefixMapping extends \InvalidArgumentException implements \PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Exception\Exception
{
    /**
     * @return $this
     */
    public static function emptyPrefixGiven()
    {
        return new self('An invalid empty string provided as a PSR mapping prefix');
    }
    /**
     * @return $this
     */
    public static function emptyPrefixMappingGiven(string $prefix)
    {
        return new self(\sprintf('An invalid empty list of paths was provided for PSR mapping prefix "%s"', $prefix));
    }
    /**
     * @return $this
     */
    public static function prefixMappingIsNotADirectory(string $prefix, string $path)
    {
        return new self(\sprintf('Provided path "%s" for prefix "%s" is not a directory', $prefix, $path));
    }
}
