<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use InvalidArgumentException;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData;
final class PhpInternalSourceLocator extends \PHPStan\BetterReflection\SourceLocator\Type\AbstractSourceLocator
{
    /** @var SourceStubber */
    private $stubber;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator, \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber $stubber)
    {
        parent::__construct($astLocator);
        $this->stubber = $stubber;
    }
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidFileLocation
     * @return \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource|null
     */
    protected function createLocatedSource(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        return $this->getClassSource($identifier) ?? $this->getFunctionSource($identifier) ?? $this->getConstantSource($identifier);
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource|null
     */
    private function getClassSource(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if (!$identifier->isClass()) {
            return null;
        }
        return $this->createLocatedSourceFromStubData($this->stubber->generateClassStub($identifier->getName()));
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource|null
     */
    private function getFunctionSource(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if (!$identifier->isFunction()) {
            return null;
        }
        return $this->createLocatedSourceFromStubData($this->stubber->generateFunctionStub($identifier->getName()));
    }
    /**
     * @return \PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource|null
     */
    private function getConstantSource(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if (!$identifier->isConstant()) {
            return null;
        }
        return $this->createLocatedSourceFromStubData($this->stubber->generateConstantStub($identifier->getName()));
    }
    /**
     * @param \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData|null $stubData
     * @return \PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource|null
     */
    private function createLocatedSourceFromStubData($stubData)
    {
        if ($stubData === null) {
            return null;
        }
        if ($stubData->getExtensionName() === null) {
            // Not internal
            return null;
        }
        return new \PHPStan\BetterReflection\SourceLocator\Located\InternalLocatedSource($stubData->getStub(), $stubData->getExtensionName(), $stubData->getFileName());
    }
}
