<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection;
/**
 * @internal
 */
final class Helpers
{
    use Nette\StaticClass;
    const PREVENT_MERGING = '_prevent_merging';
    /**
     * Merges dataset. Left has higher priority than right one.
     * @return array|string
     */
    public static function merge($value, $base)
    {
        if (\is_array($value) && isset($value[self::PREVENT_MERGING])) {
            unset($value[self::PREVENT_MERGING]);
            return $value;
        }
        if (\is_array($value) && \is_array($base)) {
            $index = 0;
            foreach ($value as $key => $val) {
                if ($key === $index) {
                    $base[] = $val;
                    $index++;
                } else {
                    $base[$key] = static::merge($val, $base[$key] ?? null);
                }
            }
            return $base;
        } elseif ($value === null && \is_array($base)) {
            return $base;
        } else {
            return $value;
        }
    }
    /**
     * @return string|null
     */
    public static function getPropertyType(\ReflectionProperty $prop)
    {
        if ($type = \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getPropertyType($prop)) {
            return ($prop->getType()->allowsNull() ? '?' : '') . $type;
        } elseif ($type = \preg_replace('#\\s.*#', '', (string) self::parseAnnotation($prop, 'var'))) {
            $class = \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getPropertyDeclaringClass($prop);
            return \preg_replace_callback('#[\\w\\\\]+#', function ($m) use($class) {
                return \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::expandClassName($m[0], $class);
            }, $type);
        }
        return null;
    }
    /**
     * Returns an annotation value.
     * @param  \ReflectionProperty  $ref
     * @return string|null
     */
    public static function parseAnnotation(\Reflector $ref, string $name)
    {
        if (!\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::areCommentsAvailable()) {
            throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
        }
        $re = '#[\\s*]@' . \preg_quote($name, '#') . '(?=\\s|$)(?:[ \\t]+([^@\\s]\\S*))?#';
        if ($ref->getDocComment() && \preg_match($re, \trim($ref->getDocComment(), '/*'), $m)) {
            return $m[1] ?? '';
        }
        return null;
    }
}
