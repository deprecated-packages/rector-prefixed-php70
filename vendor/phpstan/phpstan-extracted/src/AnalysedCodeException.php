<?php

declare (strict_types=1);
namespace PHPStan;

abstract class AnalysedCodeException extends \Exception
{
    /**
     * @return string|null
     */
    public abstract function getTip();
}
