<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Event;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Command\Command;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Output\OutputInterface;
/**
 * Allows to manipulate the exit code of a command after its execution.
 *
 * @author Francesco Levorato <git@flevour.net>
 *
 * @final since Symfony 4.4
 */
class ConsoleTerminateEvent extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Event\ConsoleEvent
{
    private $exitCode;
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Command\Command $command, \RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Output\OutputInterface $output, int $exitCode)
    {
        parent::__construct($command, $input, $output);
        $this->setExitCode($exitCode);
    }
    /**
     * Sets the exit code.
     *
     * @param int $exitCode The command exit code
     */
    public function setExitCode($exitCode)
    {
        $this->exitCode = (int) $exitCode;
    }
    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
