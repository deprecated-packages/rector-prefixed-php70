<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Config;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Configuration helpers.
 * @deprecated
 */
final class Helpers
{
    use Nette\StaticClass;
    const PREVENT_MERGING = '_prevent_merging';
    /**
     * Merges configurations. Left has higher priority than right one.
     * @return array|string
     */
    public static function merge($left, $right)
    {
        return \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::merge($left, $right);
    }
    /**
     * Return true if array prevents merging and removes this information.
     */
    public static function takeParent(&$data) : bool
    {
        if (\is_array($data) && isset($data[self::PREVENT_MERGING])) {
            unset($data[self::PREVENT_MERGING]);
            return \true;
        }
        return \false;
    }
}
