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

use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class DumpAutoloadCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('dump-autoload')->setAliases(array('dumpautoload'))->setDescription('Dumps the autoloader.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-scripts', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Skips the execution of all scripts defined in composer.json file.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('optimize', 'o', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Optimizes PSR0 and PSR4 packages to be loaded with classmaps too, good for production.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('classmap-authoritative', 'a', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Autoload classes from the classmap only. Implicitly enables `--optimize`.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('apcu', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Use APCu to cache found/not-found classes.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('apcu-prefix', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Use a custom prefix for the APCu autoloader cache. Implicitly enables --apcu'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables autoload-dev rules.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-req', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED | \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY, 'Ignore a specific platform requirement (php & ext- packages).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-reqs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Ignore all platform requirements (php & ext- packages).')))->setHelp(<<<EOT
<info>php composer.phar dump-autoload</info>

Read more at https://getcomposer.org/doc/03-cli.md#dump-autoload-dumpautoload-
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'dump-autoload', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $installationManager = $composer->getInstallationManager();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $package = $composer->getPackage();
        $config = $composer->getConfig();
        $optimize = $input->getOption('optimize') || $config->get('optimize-autoloader');
        $authoritative = $input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
        $apcuPrefix = $input->getOption('apcu-prefix');
        $apcu = $apcuPrefix !== null || $input->getOption('apcu') || $config->get('apcu-autoloader');
        if ($authoritative) {
            $this->getIO()->write('<info>Generating optimized autoload files (authoritative)</info>');
        } elseif ($optimize) {
            $this->getIO()->write('<info>Generating optimized autoload files</info>');
        } else {
            $this->getIO()->write('<info>Generating autoload files</info>');
        }
        $ignorePlatformReqs = $input->getOption('ignore-platform-reqs') ?: ($input->getOption('ignore-platform-req') ?: \false);
        $generator = $composer->getAutoloadGenerator();
        $generator->setDevMode(!$input->getOption('no-dev'));
        $generator->setClassMapAuthoritative($authoritative);
        $generator->setApcu($apcu, $apcuPrefix);
        $generator->setRunScripts(!$input->getOption('no-scripts'));
        $generator->setIgnorePlatformRequirements($ignorePlatformReqs);
        $numberOfClasses = $generator->dump($config, $localRepo, $package, $installationManager, 'composer', $optimize);
        if ($authoritative) {
            $this->getIO()->write('<info>Generated optimized autoload files (authoritative) containing ' . $numberOfClasses . ' classes</info>');
        } elseif ($optimize) {
            $this->getIO()->write('<info>Generated optimized autoload files containing ' . $numberOfClasses . ' classes</info>');
        } else {
            $this->getIO()->write('<info>Generated autoload files</info>');
        }
        return 0;
    }
}
