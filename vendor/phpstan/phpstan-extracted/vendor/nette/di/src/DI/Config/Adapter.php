<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config;

/**
 * Adapter for reading and writing configuration files.
 */
interface Adapter
{
    /**
     * Reads configuration from file.
     */
    function load(string $file) : array;
    /**
     * Generates configuration string.
     */
    function dump(array $data) : string;
}
\class_exists(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\IAdapter::class);
