<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\CommandLoader;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Command\Command;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Exception\CommandNotFoundException;
/**
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface CommandLoaderInterface
{
    /**
     * Loads a command.
     *
     * @param string $name
     *
     * @return Command
     *
     * @throws CommandNotFoundException
     */
    public function get($name);
    /**
     * Checks if a command exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);
    /**
     * @return string[] All registered command names
     */
    public function getNames();
}
