<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceStubber;

use PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData;
use PHPStan\File\FileReader;
use PHPStan\Php8StubsMap;
class Php8StubsSourceStubber implements \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber
{
    const DIRECTORY = __DIR__ . '/../../../../vendor/phpstan/php-8-stubs';
    public function hasClass(string $className) : bool
    {
        $className = \strtolower($className);
        return \array_key_exists($className, \PHPStan\Php8StubsMap::CLASSES);
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateClassStub(string $className)
    {
        $lowerClassName = \strtolower($className);
        if (!\array_key_exists($lowerClassName, \PHPStan\Php8StubsMap::CLASSES)) {
            return null;
        }
        $relativeFilePath = \PHPStan\Php8StubsMap::CLASSES[$lowerClassName];
        $file = self::DIRECTORY . '/' . $relativeFilePath;
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData(\PHPStan\File\FileReader::read($file), $this->getExtensionFromFilePath($relativeFilePath), $file);
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateFunctionStub(string $functionName)
    {
        $lowerFunctionName = \strtolower($functionName);
        if (!\array_key_exists($lowerFunctionName, \PHPStan\Php8StubsMap::FUNCTIONS)) {
            return null;
        }
        $relativeFilePath = \PHPStan\Php8StubsMap::FUNCTIONS[$lowerFunctionName];
        $file = self::DIRECTORY . '/' . $relativeFilePath;
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData(\PHPStan\File\FileReader::read($file), $this->getExtensionFromFilePath($relativeFilePath), $file);
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateConstantStub(string $constantName)
    {
        return null;
    }
    private function getExtensionFromFilePath(string $relativeFilePath) : string
    {
        $pathParts = \explode('/', $relativeFilePath);
        if ($pathParts[1] === 'Zend') {
            return 'Core';
        }
        return $pathParts[2];
    }
}
