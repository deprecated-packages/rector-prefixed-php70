<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use InvalidArgumentException;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Exception\EmptyPhpSourceCode;
use PHPStan\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
/**
 * This source locator simply parses the string given in the constructor as
 * valid PHP.
 *
 * Note that this source locator does NOT specify a filename, because we did
 * not load it from a file, so it will be null if you use this locator.
 */
class StringSourceLocator extends \PHPStan\BetterReflection\SourceLocator\Type\AbstractSourceLocator
{
    /** @var string */
    private $source;
    /**
     * @throws EmptyPhpSourceCode
     */
    public function __construct(string $source, \PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator)
    {
        parent::__construct($astLocator);
        if (empty($source)) {
            // Whilst an empty string is still "valid" PHP code, there is no
            // point in us even trying to parse it because we won't find what
            // we are looking for, therefore this throws an exception
            throw new \PHPStan\BetterReflection\SourceLocator\Exception\EmptyPhpSourceCode('Source code string was empty');
        }
        $this->source = $source;
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
        return new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource($this->source, null);
    }
}
