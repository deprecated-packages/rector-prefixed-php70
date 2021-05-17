<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflector;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function assert;
class FunctionReflector implements \PHPStan\BetterReflection\Reflector\Reflector
{
    /** @var SourceLocator */
    private $sourceLocator;
    /** @var ClassReflector */
    private $classReflector;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator, \PHPStan\BetterReflection\Reflector\ClassReflector $classReflector)
    {
        $this->sourceLocator = $sourceLocator;
        $this->classReflector = $classReflector;
    }
    /**
     * Create a ReflectionFunction for the specified $functionName.
     *
     * @return ReflectionFunction
     *
     * @throws IdentifierNotFound
     */
    public function reflect(string $functionName) : \PHPStan\BetterReflection\Reflection\Reflection
    {
        $identifier = new \PHPStan\BetterReflection\Identifier\Identifier($functionName, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_FUNCTION));
        $functionInfo = $this->sourceLocator->locateIdentifier($this->classReflector, $identifier);
        \assert($functionInfo instanceof \PHPStan\BetterReflection\Reflection\ReflectionFunction || $functionInfo === null);
        if ($functionInfo === null) {
            throw \PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound::fromIdentifier($identifier);
        }
        return $functionInfo;
    }
    /**
     * Get all the functions available in the scope specified by the SourceLocator.
     *
     * @return ReflectionFunction[]
     */
    public function getAllFunctions() : array
    {
        /** @var ReflectionFunction[] $allFunctions */
        $allFunctions = $this->sourceLocator->locateIdentifiersByType($this, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_FUNCTION));
        return $allFunctions;
    }
}
