<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\Adapters;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
/**
 * Reading and generating PHP files.
 */
final class PhpAdapter implements \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\Adapter
{
    use Nette\SmartObject;
    /**
     * Reads configuration from PHP file.
     */
    public function load(string $file) : array
    {
        return require $file;
    }
    /**
     * Generates configuration in PHP format.
     */
    public function dump(array $data) : string
    {
        return "<?php // generated by Nette \nreturn " . \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Helpers::dump($data) . ';';
    }
}
