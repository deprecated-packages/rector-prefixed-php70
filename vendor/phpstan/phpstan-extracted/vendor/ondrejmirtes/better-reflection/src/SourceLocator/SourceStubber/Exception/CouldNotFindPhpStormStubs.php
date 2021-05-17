<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber\Exception;

use RuntimeException;
class CouldNotFindPhpStormStubs extends \RuntimeException
{
    /**
     * @return $this
     */
    public static function create()
    {
        return new self('Could not find PhpStorm stubs');
    }
}
