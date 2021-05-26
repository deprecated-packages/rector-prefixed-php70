<?php

declare (strict_types=1);
namespace RectorPrefix20210526\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210526\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function in_array;
use function is_string;
use function strtolower;
final class Charset extends \RectorPrefix20210526\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    const CHARSETS = ['latin1', 'utf-8', 'utf-8-bom', 'utf-16be', 'utf-16le'];
    /**
     * @inheritdoc
     * @return void
     */
    public function validateValue($value)
    {
        if (\is_string($value) === \false || \in_array(\strtolower($value), self::CHARSETS) === \false) {
            throw new \RectorPrefix20210526\Idiosyncratic\EditorConfig\Exception\InvalidValue($this->getStringValue(), $this->getName());
        }
    }
    public function getName() : string
    {
        return 'charset';
    }
}
