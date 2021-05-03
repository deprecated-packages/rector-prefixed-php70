<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210503\Composer\Command;

use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
/**
 * @author Davey Shafik <me@daveyshafik.com>
 */
class ExecCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('exec')->setDescription('Executes a vendored binary/script.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('list', 'l', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('binary', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'The binary to run, e.g. phpunit'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('args', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Arguments to pass to the binary. Use <info>--</info> to separate from composer arguments')))->setHelp(<<<EOT
Executes a vendored binary/script.

Read more at https://getcomposer.org/doc/03-cli.md#exec
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $binDir = $composer->getConfig()->get('bin-dir');
        if ($input->getOption('list') || !$input->getArgument('binary')) {
            $bins = \glob($binDir . '/*');
            $bins = \array_merge($bins, \array_map(function ($e) {
                return "{$e} (local)";
            }, $composer->getPackage()->getBinaries()));
            if (!$bins) {
                throw new \RuntimeException("No binaries found in composer.json or in bin-dir ({$binDir})");
            }
            $this->getIO()->write(<<<EOT
<comment>Available binaries:</comment>
EOT
);
            foreach ($bins as $bin) {
                // skip .bat copies
                if (isset($previousBin) && $bin === $previousBin . '.bat') {
                    continue;
                }
                $previousBin = $bin;
                $bin = \basename($bin);
                $this->getIO()->write(<<<EOT
<info>- {$bin}</info>
EOT
);
            }
            return 0;
        }
        $binary = $input->getArgument('binary');
        $dispatcher = $composer->getEventDispatcher();
        $dispatcher->addListener('__exec_command', $binary);
        // If the CWD was modified, we restore it to what it was initially, as it was
        // most likely modified by the global command, and we want exec to run in the local working directory
        // not the global one
        if (\getcwd() !== $this->getApplication()->getInitialWorkingDirectory()) {
            try {
                \chdir($this->getApplication()->getInitialWorkingDirectory());
            } catch (\Exception $e) {
                throw new \RuntimeException('Could not switch back to working directory "' . $this->getApplication()->getInitialWorkingDirectory() . '"', 0, $e);
            }
        }
        return $dispatcher->dispatchScript('__exec_command', \true, $input->getArgument('args'));
    }
}
