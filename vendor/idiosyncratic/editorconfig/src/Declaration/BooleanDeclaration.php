<?php

declare (strict_types=1);
namespace RectorPrefix20210522\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210522\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function is_bool;
abstract class BooleanDeclaration extends \RectorPrefix20210522\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    /**
     * @inheritdoc
     * @return void
     */
    public function validateValue($value)
    {
        if (\is_bool($value) === \false) {
            throw new \RectorPrefix20210522\Idiosyncratic\EditorConfig\Exception\InvalidValue($this->getStringValue(), $this->getName());
        }
    }
}
