<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Located;

class InternalLocatedSource extends \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource
{
    /** @var string */
    private $extensionName;
    /**
     * {@inheritDoc}
     * @param string|null $fileName
     */
    public function __construct(string $source, string $extensionName, $fileName = null)
    {
        parent::__construct($source, $fileName);
        $this->extensionName = $extensionName;
    }
    public function isInternal() : bool
    {
        return \true;
    }
    /**
     * @return string|null
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }
}
