<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber;

/**
 * @internal
 */
interface SourceStubber
{
    public function hasClass(string $className) : bool;
    /**
     * Generates stub for given class. Returns null when it cannot generate the stub.
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateClassStub(string $className);
    /**
     * Generates stub for given function. Returns null when it cannot generate the stub.
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateFunctionStub(string $functionName);
    /**
     * Generates stub for given constant. Returns null when it cannot generate the stub.
     * @return \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null
     */
    public function generateConstantStub(string $constantName);
}
