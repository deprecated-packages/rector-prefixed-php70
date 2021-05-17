<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber;

use function array_merge;
use function array_reduce;
class AggregateSourceStubber implements \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber
{
    /** @var SourceStubber[] */
    private $sourceStubbers;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber $sourceStubber, \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber ...$otherSourceStubbers)
    {
        $this->sourceStubbers = \array_merge([$sourceStubber], $otherSourceStubbers);
    }
    public function hasClass(string $className) : bool
    {
        foreach ($this->sourceStubbers as $sourceStubber) {
            if ($sourceStubber->hasClass($className)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateClassStub(string $className)
    {
        foreach ($this->sourceStubbers as $sourceStubber) {
            $stubData = $sourceStubber->generateClassStub($className);
            if ($stubData !== null) {
                return $stubData;
            }
        }
        return null;
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateFunctionStub(string $functionName)
    {
        foreach ($this->sourceStubbers as $sourceStubber) {
            $stubData = $sourceStubber->generateFunctionStub($functionName);
            if ($stubData !== null) {
                return $stubData;
            }
        }
        return null;
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateConstantStub(string $constantName)
    {
        return \array_reduce($this->sourceStubbers, static function ($stubData, \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber $sourceStubber) use($constantName) {
            return $stubData ?? $sourceStubber->generateConstantStub($constantName);
        }, null);
    }
}
