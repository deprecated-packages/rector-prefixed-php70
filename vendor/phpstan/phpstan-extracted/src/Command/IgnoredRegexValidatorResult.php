<?php

declare (strict_types=1);
namespace PHPStan\Command;

class IgnoredRegexValidatorResult
{
    /** @var array<string, string> */
    private $ignoredTypes;
    /** @var bool */
    private $anchorsInTheMiddle;
    /** @var bool */
    private $allErrorsIgnored;
    /** @var string|null */
    private $wrongSequence;
    /** @var string|null */
    private $escapedWrongSequence;
    /**
     * @param array<string, string> $ignoredTypes
     * @param bool $anchorsInTheMiddle
     * @param bool $allErrorsIgnored
     * @param string|null $wrongSequence
     * @param string|null $escapedWrongSequence
     */
    public function __construct(array $ignoredTypes, bool $anchorsInTheMiddle, bool $allErrorsIgnored, $wrongSequence = null, $escapedWrongSequence = null)
    {
        $this->ignoredTypes = $ignoredTypes;
        $this->anchorsInTheMiddle = $anchorsInTheMiddle;
        $this->allErrorsIgnored = $allErrorsIgnored;
        $this->wrongSequence = $wrongSequence;
        $this->escapedWrongSequence = $escapedWrongSequence;
    }
    /**
     * @return array<string, string>
     */
    public function getIgnoredTypes() : array
    {
        return $this->ignoredTypes;
    }
    public function hasAnchorsInTheMiddle() : bool
    {
        return $this->anchorsInTheMiddle;
    }
    public function areAllErrorsIgnored() : bool
    {
        return $this->allErrorsIgnored;
    }
    /**
     * @return string|null
     */
    public function getWrongSequence()
    {
        return $this->wrongSequence;
    }
    /**
     * @return string|null
     */
    public function getEscapedWrongSequence()
    {
        return $this->escapedWrongSequence;
    }
}
