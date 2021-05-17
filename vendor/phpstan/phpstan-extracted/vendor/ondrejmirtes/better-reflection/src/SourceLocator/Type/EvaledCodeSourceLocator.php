<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use InvalidArgumentException;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use PHPStan\BetterReflection\SourceLocator\Located\EvaledLocatedSource;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber;
use ReflectionClass;
use function class_exists;
use function file_exists;
use function interface_exists;
use function trait_exists;
final class EvaledCodeSourceLocator extends \PHPStan\BetterReflection\SourceLocator\Type\AbstractSourceLocator
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
        $classReflection = $this->getInternalReflectionClass($identifier);
        if ($classReflection === null) {
            return null;
        }
        $stubData = $this->stubber->generateClassStub($classReflection->getName());
        if ($stubData === null) {
            return null;
        }
        return new \PHPStan\BetterReflection\SourceLocator\Located\EvaledLocatedSource($stubData->getStub());
    }
    /**
     * @return \ReflectionClass|null
     */
    private function getInternalReflectionClass(\PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if (!$identifier->isClass()) {
            return null;
        }
        $name = $identifier->getName();
        if (!(\class_exists($name, \false) || \interface_exists($name, \false) || \trait_exists($name, \false))) {
            return null;
            // not an available internal class
        }
        $reflection = new \ReflectionClass($name);
        $sourceFile = $reflection->getFileName();
        return $sourceFile && \file_exists($sourceFile) ? null : $reflection;
    }
}
