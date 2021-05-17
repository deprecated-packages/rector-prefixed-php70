<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator;

use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use function file_exists;
use function is_file;
use function is_readable;
use function sprintf;
class FileChecker
{
    /**
     * @throws InvalidFileLocation
     * @return void
     */
    public static function assertReadableFile(string $filename)
    {
        if (empty($filename)) {
            throw new \PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation('Filename was empty');
        }
        if (!\file_exists($filename)) {
            throw new \PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation(\sprintf('File "%s" does not exist', $filename));
        }
        if (!\is_readable($filename)) {
            throw new \PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation(\sprintf('File "%s" is not readable', $filename));
        }
        if (!\is_file($filename)) {
            throw new \PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation(\sprintf('"%s" is not a file', $filename));
        }
    }
}
