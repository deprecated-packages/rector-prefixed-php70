<?php

declare (strict_types=1);
namespace PHPStan\File;

class FuzzyRelativePathHelper implements \PHPStan\File\RelativePathHelper
{
    /** @var RelativePathHelper */
    private $fallbackRelativePathHelper;
    /** @var string */
    private $directorySeparator;
    /** @var string|null */
    private $pathToTrim = null;
    /**
     * @param RelativePathHelper $fallbackRelativePathHelper
     * @param string $currentWorkingDirectory
     * @param string[] $analysedPaths
     * @param string|null $directorySeparator
     */
    public function __construct(\PHPStan\File\RelativePathHelper $fallbackRelativePathHelper, string $currentWorkingDirectory, array $analysedPaths, $directorySeparator = null)
    {
        $this->fallbackRelativePathHelper = $fallbackRelativePathHelper;
        if ($directorySeparator === null) {
            $directorySeparator = \DIRECTORY_SEPARATOR;
        }
        $this->directorySeparator = $directorySeparator;
        $pathBeginning = null;
        $pathToTrimArray = null;
        $trimBeginning = static function (string $path) : array {
            if (\substr($path, 0, 1) === '/') {
                return ['/', \substr($path, 1)];
            } elseif (\substr($path, 1, 1) === ':') {
                return [\substr($path, 0, 3), \substr($path, 3)];
            }
            return ['', $path];
        };
        if (!\in_array($currentWorkingDirectory, ['', '/'], \true) && !(\strlen($currentWorkingDirectory) === 3 && \substr($currentWorkingDirectory, 1, 1) === ':')) {
            list($pathBeginning, $currentWorkingDirectory) = $trimBeginning($currentWorkingDirectory);
            /** @var string[] $pathToTrimArray */
            $pathToTrimArray = \explode($directorySeparator, $currentWorkingDirectory);
        }
        foreach ($analysedPaths as $pathNumber => $path) {
            list($tempPathBeginning, $path) = $trimBeginning($path);
            /** @var string[] $pathArray */
            $pathArray = \explode($directorySeparator, $path);
            $pathTempParts = [];
            foreach ($pathArray as $i => $pathPart) {
                if (\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Strings::endsWith($pathPart, '.php')) {
                    continue;
                }
                if (!isset($pathToTrimArray[$i])) {
                    if ($pathNumber !== 0) {
                        $pathToTrimArray = $pathTempParts;
                        continue 2;
                    }
                } elseif ($pathToTrimArray[$i] !== $pathPart) {
                    $pathToTrimArray = $pathTempParts;
                    continue 2;
                }
                $pathTempParts[] = $pathPart;
            }
            $pathBeginning = $tempPathBeginning;
            $pathToTrimArray = $pathTempParts;
        }
        if ($pathToTrimArray === null || \count($pathToTrimArray) === 0) {
            return;
        }
        $pathToTrim = $pathBeginning . \implode($directorySeparator, $pathToTrimArray);
        $realPathToTrim = \realpath($pathToTrim);
        if ($realPathToTrim !== \false) {
            $pathToTrim = $realPathToTrim;
        }
        $this->pathToTrim = $pathToTrim;
    }
    public function getRelativePath(string $filename) : string
    {
        if ($this->pathToTrim !== null && \strpos($filename, $this->pathToTrim) === 0) {
            return \ltrim(\substr($filename, \strlen($this->pathToTrim)), $this->directorySeparator);
        }
        return $this->fallbackRelativePathHelper->getRelativePath($filename);
    }
}
