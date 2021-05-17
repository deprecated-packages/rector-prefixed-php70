<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber;

/**
 * @internal
 */
class StubData
{
    /** @var string */
    private $stub;
    /** @var string|null */
    private $extensionName;
    /** @var string|null */
    private $fileName;
    /**
     * @param string|null $extensionName
     * @param string|null $fileName
     */
    public function __construct(string $stub, $extensionName, $fileName = null)
    {
        $this->stub = $stub;
        $this->extensionName = $extensionName;
        $this->fileName = $fileName;
    }
    public function getStub() : string
    {
        return $this->stub;
    }
    /**
     * @return string|null
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }
    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
