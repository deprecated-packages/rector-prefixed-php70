<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use function array_map;
use function array_merge;
class AggregateSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var SourceLocator[] */
    private $sourceLocators;
    /**
     * @param SourceLocator[] $sourceLocators
     */
    public function __construct(array $sourceLocators = [])
    {
        // This slightly confusing code simply type-checks the $sourceLocators
        // array by unpacking them and splatting them in the closure.
        $validator = static function (\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator ...$sourceLocator) : array {
            return $sourceLocator;
        };
        $this->sourceLocators = $validator(...$sourceLocators);
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        foreach ($this->sourceLocators as $sourceLocator) {
            $located = $sourceLocator->locateIdentifier($reflector, $identifier);
            if ($located) {
                return $located;
            }
        }
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return \array_merge([], ...\array_map(static function (\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator) use($reflector, $identifierType) {
            return $sourceLocator->locateIdentifiersByType($reflector, $identifierType);
        }, $this->sourceLocators));
    }
}
