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

use RectorPrefix20210503\Composer\Script\Event as ScriptEvent;
use RectorPrefix20210503\Composer\Script\ScriptEvents;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Fabien Potencier <fabien.potencier@gmail.com>
 */
class RunScriptCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    /**
     * @var array Array with command events
     */
    protected $scriptEvents = array(\RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_INSTALL_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_INSTALL_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_UPDATE_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_UPDATE_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_STATUS_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_STATUS_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_ROOT_PACKAGE_INSTALL, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_CREATE_PROJECT_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_ARCHIVE_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_ARCHIVE_CMD, \RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_AUTOLOAD_DUMP, \RectorPrefix20210503\Composer\Script\ScriptEvents::POST_AUTOLOAD_DUMP);
    protected function configure()
    {
        $this->setName('run-script')->setAliases(array('run'))->setDescription('Runs the scripts defined in composer.json.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('script', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Script name to run.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('args', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, ''), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('timeout', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Sets script timeout in seconds, or 0 for never.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Sets the dev mode.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables the dev mode.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('list', 'l', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'List scripts.')))->setHelp(<<<EOT
The <info>run-script</info> command runs scripts defined in composer.json:

<info>php composer.phar run-script post-update-cmd</info>

Read more at https://getcomposer.org/doc/03-cli.md#run-script
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if ($input->getOption('list')) {
            return $this->listScripts($output);
        }
        if (!$input->getArgument('script')) {
            throw new \RuntimeException('Missing required argument "script"');
        }
        $script = $input->getArgument('script');
        if (!\in_array($script, $this->scriptEvents)) {
            if (\defined('Composer\\Script\\ScriptEvents::' . \str_replace('-', '_', \strtoupper($script)))) {
                throw new \InvalidArgumentException(\sprintf('Script "%s" cannot be run with this command', $script));
            }
        }
        $composer = $this->getComposer();
        $devMode = $input->getOption('dev') || !$input->getOption('no-dev');
        $event = new \RectorPrefix20210503\Composer\Script\Event($script, $composer, $this->getIO(), $devMode);
        $hasListeners = $composer->getEventDispatcher()->hasEventListeners($event);
        if (!$hasListeners) {
            throw new \InvalidArgumentException(\sprintf('Script "%s" is not defined in this package', $script));
        }
        $args = $input->getArgument('args');
        if (null !== ($timeout = $input->getOption('timeout'))) {
            if (!\ctype_digit($timeout)) {
                throw new \RuntimeException('Timeout value must be numeric and positive if defined, or 0 for forever');
            }
            // Override global timeout set before in Composer by environment or config
            \RectorPrefix20210503\Composer\Util\ProcessExecutor::setTimeout((int) $timeout);
        }
        return $composer->getEventDispatcher()->dispatchScript($script, $devMode, $args);
    }
    protected function listScripts(\RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $scripts = $this->getComposer()->getPackage()->getScripts();
        if (!\count($scripts)) {
            return 0;
        }
        $io = $this->getIO();
        $io->writeError('<info>scripts:</info>');
        $table = array();
        foreach ($scripts as $name => $script) {
            $description = '';
            try {
                $cmd = $this->getApplication()->find($name);
                if ($cmd instanceof \RectorPrefix20210503\Composer\Command\ScriptAliasCommand) {
                    $description = $cmd->getDescription();
                }
            } catch (\RectorPrefix20210503\Symfony\Component\Console\Exception\CommandNotFoundException $e) {
                // ignore scripts that have no command associated, like native Composer script listeners
            }
            $table[] = array('  ' . $name, $description);
        }
        $this->renderTable($table, $output);
        return 0;
    }
}
