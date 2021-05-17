<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use function array_key_exists;
use function get_class;
use function spl_object_hash;
final class MemoizingSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var SourceLocator */
    private $wrappedSourceLocator;
    /** @var Reflection[]|null[] indexed by reflector key and identifier cache key */
    private $cacheByIdentifierKeyAndOid = [];
    /** @var Reflection[][] indexed by reflector key and identifier type cache key */
    private $cacheByIdentifierTypeKeyAndOid = [];
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $wrappedSourceLocator)
    {
        $this->wrappedSourceLocator = $wrappedSourceLocator;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\Reflection|null
     */
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier)
    {
        $cacheKey = $this->reflectorCacheKey($reflector) . '_' . $this->identifierToCacheKey($identifier);
        if (\array_key_exists($cacheKey, $this->cacheByIdentifierKeyAndOid)) {
            return $this->cacheByIdentifierKeyAndOid[$cacheKey];
        }
        return $this->cacheByIdentifierKeyAndOid[$cacheKey] = $this->wrappedSourceLocator->locateIdentifier($reflector, $identifier);
    }
    /**
     * @return Reflection[]
     */
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        $cacheKey = $this->reflectorCacheKey($reflector) . '_' . $this->identifierTypeToCacheKey($identifierType);
        if (\array_key_exists($cacheKey, $this->cacheByIdentifierTypeKeyAndOid)) {
            return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey];
        }
        return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey] = $this->wrappedSourceLocator->locateIdentifiersByType($reflector, $identifierType);
    }
    private function reflectorCacheKey(\PHPStan\BetterReflection\Reflector\Reflector $reflector) : string
    {
        return 'type:' . \get_class($reflector) . '#oid:' . \spl_object_hash($reflector);
    }
    private function identifierToCacheKey(\PHPStan\BetterReflection\Identifier\Identifier $identifier) : string
    {
        return $this->identifierTypeToCacheKey($identifier->getType()) . '#name:' . $identifier->getName();
    }
    private function identifierTypeToCacheKey(\PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : string
    {
        return 'type:' . $identifierType->getName();
    }
}
