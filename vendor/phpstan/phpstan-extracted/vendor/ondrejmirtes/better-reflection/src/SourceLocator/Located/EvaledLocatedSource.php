<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Located;

class EvaledLocatedSource extends \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $source)
    {
        parent::__construct($source, null);
    }
    public function isEvaled() : bool
    {
        return \true;
    }
}
