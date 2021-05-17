<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator as AstLocator;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
abstract class AbstractSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var AstLocator */
    private $astLocator;
    /**
     * Children should implement this method and return a LocatedSource object
     * which contains the source and the file from which it was located.
     *
     * @example
     *   return new LocatedSource(['<?php class Foo {}', null]);
     *   return new LocatedSource([\file_get_contents('Foo.php'), 'Foo.php']);
     * @return \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource|null
     */
    protected abstract function createLocatedSource(\PHPStan\BetterReflection\Identifier\Identifier $identifier);
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator)
    {
        $this->astLocator = $astLocator;
    }
    /**
     * {@inheritDoc}
     *
     * @throws ParseToAstFailure
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        $locatedSource = $this->createLocatedSource($identifier);
        if (!$locatedSource) {
            return null;
        }
        try {
            return $this->astLocator->findReflection($reflector, $locatedSource, $identifier);
        } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $exception) {
            return null;
        }
    }
    /**
     * {@inheritDoc}
     *
     * @throws ParseToAstFailure
     */
    public final function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        $locatedSource = $this->createLocatedSource(new \PHPStan\BetterReflection\Identifier\Identifier(\PHPStan\BetterReflection\Identifier\Identifier::WILDCARD, $identifierType));
        if (!$locatedSource) {
            return [];
        }
        return $this->astLocator->findReflectionsOfType($reflector, $locatedSource, $identifierType);
    }
}
