<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util;

use InvalidArgumentException;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflection\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function array_merge;
use function method_exists;
final class FindReflectionOnLine
{
    /** @var SourceLocator */
    private $sourceLocator;
    /** @var Locator */
    private $astLocator;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator, \PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator)
    {
        $this->sourceLocator = $sourceLocator;
        $this->astLocator = $astLocator;
    }
    /**
     * Find a reflection on the specified line number.
     *
     * Returns null if no reflections found on the line.
     *
     * @return ReflectionMethod|ReflectionClass|ReflectionFunction|ReflectionConstant|Reflection|null
     *
     * @throws InvalidFileLocation
     * @throws ParseToAstFailure
     * @throws InvalidArgumentException
     */
    public function __invoke(string $filename, int $lineNumber)
    {
        $reflections = $this->computeReflections($filename);
        foreach ($reflections as $reflection) {
            if ($reflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionClass && $this->containsLine($reflection, $lineNumber)) {
                foreach ($reflection->getMethods() as $method) {
                    if ($this->containsLine($method, $lineNumber)) {
                        return $method;
                    }
                }
                return $reflection;
            }
            if ($reflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionFunction && $this->containsLine($reflection, $lineNumber)) {
                return $reflection;
            }
            if ($reflection instanceof \PHPStan\BetterReflection\Reflection\ReflectionConstant && $this->containsLine($reflection, $lineNumber)) {
                return $reflection;
            }
        }
        return null;
    }
    /**
     * Find all class and function reflections in the specified file
     *
     * @return Reflection[]
     *
     * @throws ParseToAstFailure
     * @throws InvalidFileLocation
     */
    private function computeReflections(string $filename) : array
    {
        $singleFileSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\SingleFileSourceLocator($filename, $this->astLocator);
        $reflector = new \PHPStan\BetterReflection\Reflector\ClassReflector(new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator([$singleFileSourceLocator, $this->sourceLocator]));
        return \array_merge($singleFileSourceLocator->locateIdentifiersByType($reflector, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_CLASS)), $singleFileSourceLocator->locateIdentifiersByType($reflector, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_FUNCTION)), $singleFileSourceLocator->locateIdentifiersByType($reflector, new \PHPStan\BetterReflection\Identifier\IdentifierType(\PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_CONSTANT)));
    }
    /**
     * Check to see if the line is within the boundaries of the reflection specified.
     *
     * @param ReflectionMethod|ReflectionClass|ReflectionFunction|Reflection $reflection
     *
     * @throws InvalidArgumentException
     */
    private function containsLine($reflection, int $lineNumber) : bool
    {
        if (!\method_exists($reflection, 'getStartLine')) {
            throw new \InvalidArgumentException('Reflection does not have getStartLine method');
        }
        if (!\method_exists($reflection, 'getEndLine')) {
            throw new \InvalidArgumentException('Reflection does not have getEndLine method');
        }
        return $lineNumber >= $reflection->getStartLine() && $lineNumber <= $reflection->getEndLine();
    }
}
