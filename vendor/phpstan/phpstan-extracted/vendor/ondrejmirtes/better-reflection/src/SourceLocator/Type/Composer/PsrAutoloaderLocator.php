<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type\Composer;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\PsrAutoloaderMapping;
use PHPStan\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function file_exists;
use function file_get_contents;
final class PsrAutoloaderLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var PsrAutoloaderMapping */
    private $mapping;
    /** @var Locator */
    private $astLocator;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\PsrAutoloaderMapping $mapping, \PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator)
    {
        $this->mapping = $mapping;
        $this->astLocator = $astLocator;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        foreach ($this->mapping->resolvePossibleFilePaths($identifier) as $file) {
            if (!\file_exists($file)) {
                continue;
            }
            try {
                return $this->astLocator->findReflection($reflector, new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource(\file_get_contents($file), $file), $identifier);
            } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $exception) {
                // on purpose - autoloading is allowed to fail, and silently-failing autoloaders are normal/endorsed
            }
        }
        return null;
    }
    /**
     * Find all identifiers of a type
     *
     * @return Reflection[]
     */
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return (new \PHPStan\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator($this->mapping->directories(), $this->astLocator))->locateIdentifiersByType($reflector, $identifierType);
    }
}
