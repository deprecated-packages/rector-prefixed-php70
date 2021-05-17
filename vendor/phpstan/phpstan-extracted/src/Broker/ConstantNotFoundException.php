<?php

declare (strict_types=1);
namespace PHPStan\Broker;

class ConstantNotFoundException extends \PHPStan\AnalysedCodeException
{
    /** @var string */
    private $constantName;
    public function __construct(string $constantName)
    {
        parent::__construct(\sprintf('Constant %s not found.', $constantName));
        $this->constantName = $constantName;
    }
    public function getConstantName() : string
    {
        return $this->constantName;
    }
    /**
     * @return string|null
     */
    public function getTip()
    {
        return 'Learn more at https://phpstan.org/user-guide/discovering-symbols';
    }
}
