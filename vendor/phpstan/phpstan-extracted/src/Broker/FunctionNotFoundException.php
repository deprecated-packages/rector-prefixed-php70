<?php

declare (strict_types=1);
namespace PHPStan\Broker;

class FunctionNotFoundException extends \PHPStan\AnalysedCodeException
{
    /** @var string */
    private $functionName;
    public function __construct(string $functionName)
    {
        parent::__construct(\sprintf('Function %s not found while trying to analyse it - discovering symbols is probably not configured properly.', $functionName));
        $this->functionName = $functionName;
    }
    public function getFunctionName() : string
    {
        return $this->functionName;
    }
    /**
     * @return string|null
     */
    public function getTip()
    {
        return 'Learn more at https://phpstan.org/user-guide/discovering-symbols';
    }
}
