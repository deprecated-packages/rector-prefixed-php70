<?php

declare (strict_types=1);
namespace PHPStan\Broker;

class ClassAutoloadingException extends \PHPStan\AnalysedCodeException
{
    /** @var string */
    private $className;
    /**
     * @param \Throwable|null $previous
     */
    public function __construct(string $functionName, $previous = null)
    {
        if ($previous !== null) {
            parent::__construct(\sprintf('%s (%s) thrown while looking for class %s.', \get_class($previous), $previous->getMessage(), $functionName), 0, $previous);
        } else {
            parent::__construct(\sprintf('Class %s not found.', $functionName), 0);
        }
        $this->className = $functionName;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    /**
     * @return string|null
     */
    public function getTip()
    {
        return 'Learn more at https://phpstan.org/user-guide/discovering-symbols';
    }
}
