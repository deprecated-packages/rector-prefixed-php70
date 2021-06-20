<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Strings;
class ClassNameHelper
{
    public static function isValidClassName(string $name) : bool
    {
        // from https://stackoverflow.com/questions/3195614/validate-class-method-names-with-regex#comment104531582_12011255
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Strings::match(\ltrim($name, '\\'), '/^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*(\\\\[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)*$/') !== null;
    }
}
