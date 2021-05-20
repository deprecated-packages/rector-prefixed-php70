<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use function file_get_contents;
/**
 * This source locator uses Composer's built-in ClassLoader to locate files.
 *
 * Note that we use ClassLoader->findFile directory, rather than
 * ClassLoader->loadClass because this library has a strict requirement that we
 * do NOT actually load the classes
 */
class ComposerSourceLocator extends \PHPStan\BetterReflection\SourceLocator\Type\AbstractSourceLocator
{
    /** @var ClassLoader */
    private $classLoader;
    public function __construct(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Composer\Autoload\ClassLoader $classLoader, \PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator)
    {
        parent::__construct($astLocator);
        $this->classLoader = $classLoader;
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
        if ($identifier->getType()->getName() !== \PHPStan\BetterReflection\Identifier\IdentifierType::IDENTIFIER_CLASS) {
            return null;
        }
        $filename = $this->classLoader->findFile($identifier->getName());
        if (!$filename) {
            return null;
        }
        return new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource(\file_get_contents($filename), $filename);
    }
}
