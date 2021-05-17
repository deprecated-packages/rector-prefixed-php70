<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Exception;

use LogicException;
class EvaledAnonymousClassCannotBeLocated extends \LogicException
{
    /**
     * @return $this
     */
    public static function create()
    {
        return new self('Evaled anonymous class cannot be located');
    }
}
