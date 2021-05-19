<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
class ClassNameHelper
{
    public static function isValidClassName(string $name) : bool
    {
        // from https://stackoverflow.com/questions/3195614/validate-class-method-names-with-regex#comment104531582_12011255
        return \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($name, '/^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*(\\\\[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)*$/') !== null;
    }
}
