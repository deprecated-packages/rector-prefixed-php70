<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceLocator;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
class ClassWhitelistSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var SourceLocator */
    private $sourceLocator;
    /** @var string[] */
    private $patterns;
    /**
     * @param SourceLocator $sourceLocator
     * @param string[] $patterns
     */
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator, array $patterns)
    {
        $this->sourceLocator = $sourceLocator;
        $this->patterns = $patterns;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        if ($identifier->isClass()) {
            foreach ($this->patterns as $pattern) {
                if (\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($identifier->getName(), $pattern) !== null) {
                    return $this->sourceLocator->locateIdentifier($reflector, $identifier);
                }
            }
            return null;
        }
        return $this->sourceLocator->locateIdentifier($reflector, $identifier);
    }
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return $this->sourceLocator->locateIdentifiersByType($reflector, $identifierType);
    }
}
