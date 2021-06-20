<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Extensions;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
/**
 * Enables registration of other extensions in $config file
 */
final class ExtensionsExtension extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\CompilerExtension
{
    public function getConfigSchema() : \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Schema
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::arrayOf('RectorPrefix20210620\\string|_HumbugBox15516bb2b566\\Nette\\DI\\Definitions\\Statement');
    }
    public function loadConfiguration()
    {
        foreach ($this->getConfig() as $name => $class) {
            if (\is_int($name)) {
                $name = null;
            }
            $args = [];
            if ($class instanceof \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Definitions\Statement) {
                list($class, $args) = [$class->getEntity(), $class->arguments];
            }
            if (!\is_a($class, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\CompilerExtension::class, \true)) {
                throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\InvalidConfigurationException("Extension '{$class}' not found or is not Nette\\DI\\CompilerExtension descendant.");
            }
            $this->compiler->addExtension($name, (new \ReflectionClass($class))->newInstanceArgs($args));
        }
    }
}
