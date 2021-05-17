<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflector;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function assert;
class ConstantReflector implements \PHPStan\BetterReflection\Reflector\Reflector
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
     * Create a ReflectionConstant for the specified $constantName.
     *
     * @return ReflectionConstant
     *
     * @throws IdentifierNotFound
     */
    public function reflect(string $constantName) : \PHPStan\BetterReflection\Reflection\Reflection
    {
        $identifier = new \PHPStan\BetterReflection\Identifier\Identifier($constantName, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_CONSTANT));
        $constantInfo = $this->sourceLocator->locateIdentifier($this->classReflector, $identifier);
        \assert($constantInfo instanceof \PHPStan\BetterReflection\Reflection\ReflectionConstant || $constantInfo === null);
        if ($constantInfo === null) {
            throw \PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound::fromIdentifier($identifier);
        }
        return $constantInfo;
    }
    /**
     * Get all the constants available in the scope specified by the SourceLocator.
     *
     * @return array<int, ReflectionConstant>
     */
    public function getAllConstants() : array
    {
        /** @var array<int,ReflectionConstant> $allConstants */
        $allConstants = $this->sourceLocator->locateIdentifiersByType($this, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_CONSTANT));
        return $allConstants;
    }
}
