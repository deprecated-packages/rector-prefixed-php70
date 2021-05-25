<?php

declare (strict_types=1);
namespace PHPStan\File;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
class FileHelper
{
    /** @var string */
    private $workingDirectory;
    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = $this->normalizePath($workingDirectory);
    }
    public function getWorkingDirectory() : string
    {
        return $this->workingDirectory;
    }
    public function absolutizePath(string $path) : string
    {
        if (\DIRECTORY_SEPARATOR === '/') {
            if (\substr($path, 0, 1) === '/') {
                return $path;
            }
        } else {
            if (\substr($path, 1, 1) === ':') {
                return $path;
            }
        }
        if (\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::startsWith($path, 'phar://')) {
            return $path;
        }
        return \rtrim($this->getWorkingDirectory(), '/\\') . \DIRECTORY_SEPARATOR . \ltrim($path, '/\\');
    }
    public function normalizePath(string $originalPath, string $directorySeparator = \DIRECTORY_SEPARATOR) : string
    {
        $matches = \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($originalPath, '~^([a-z]+)\\:\\/\\/(.+)~');
        if ($matches !== null) {
            list(, $scheme, $path) = $matches;
        } else {
            $scheme = null;
            $path = $originalPath;
        }
        $path = \str_replace('\\', '/', $path);
        $path = \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::replace($path, '~/{2,}~', '/');
        $pathRoot = \strpos($path, '/') === 0 ? $directorySeparator : '';
        $pathParts = \explode('/', \trim($path, '/'));
        $normalizedPathParts = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }
            if ($pathPart === '..') {
                /** @var string $removedPart */
                $removedPart = \array_pop($normalizedPathParts);
                if ($scheme === 'phar' && \substr($removedPart, -5) === '.phar') {
                    $scheme = null;
                }
            } else {
                $normalizedPathParts[] = $pathPart;
            }
        }
        return ($scheme !== null ? $scheme . '://' : '') . $pathRoot . \implode($directorySeparator, $normalizedPathParts);
    }
}
