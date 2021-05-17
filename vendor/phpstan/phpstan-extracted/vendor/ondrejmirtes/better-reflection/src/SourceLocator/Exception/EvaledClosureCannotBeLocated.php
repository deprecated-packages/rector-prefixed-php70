<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Exception;

use LogicException;
class EvaledClosureCannotBeLocated extends \LogicException
{
    /**
     * @return $this
     */
    public static function create()
    {
        return new self('Evaled closure cannot be located');
    }
}
