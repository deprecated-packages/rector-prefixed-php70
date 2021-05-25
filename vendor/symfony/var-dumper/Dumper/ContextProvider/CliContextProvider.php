<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210525\Symfony\Component\VarDumper\Dumper\ContextProvider;

/**
 * Tries to provide context on CLI.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
final class CliContextProvider implements \RectorPrefix20210525\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface
{
    /**
     * @return mixed[]|null
     */
    public function getContext()
    {
        if ('cli' !== \PHP_SAPI) {
            return null;
        }
        return ['command_line' => $commandLine = \implode(' ', $_SERVER['argv'] ?? []), 'identifier' => \hash('crc32b', $commandLine . $_SERVER['REQUEST_TIME_FLOAT'])];
    }
}
