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

use RectorPrefix20210503\Composer\Factory;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\Composer;
use RectorPrefix20210503\Composer\Repository\CompositeRepository;
use RectorPrefix20210503\Composer\Repository\RepositoryFactory;
use RectorPrefix20210503\Composer\Script\ScriptEvents;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Util\Loop;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * Creates an archive of a package for distribution.
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class ArchiveCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('archive')->setDescription('Creates an archive of this composer package.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('package', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'The package to archive instead of the current project'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('version', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'A version constraint to find the package to archive'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('format', 'f', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Format of the resulting archive: tar or zip'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dir', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Write the archive to this directory'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('file', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Write the archive with the given file name.' . ' Note that the format will be appended.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-filters', \false, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Ignore filters when saving package')))->setHelp(<<<EOT
The <info>archive</info> command creates an archive of the specified format
containing the files and directories of the Composer project or the specified
package in the specified version and writes it to the specified directory.

<info>php composer.phar archive [--format=zip] [--dir=/foo] [package [version]]</info>

Read more at https://getcomposer.org/doc/03-cli.md#archive
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer(\false);
        $config = null;
        if ($composer) {
            $config = $composer->getConfig();
            $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'archive', $input, $output);
            $eventDispatcher = $composer->getEventDispatcher();
            $eventDispatcher->dispatch($commandEvent->getName(), $commandEvent);
            $eventDispatcher->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_ARCHIVE_CMD);
        }
        if (!$config) {
            $config = \RectorPrefix20210503\Composer\Factory::createConfig();
        }
        if (null === $input->getOption('format')) {
            $input->setOption('format', $config->get('archive-format'));
        }
        if (null === $input->getOption('dir')) {
            $input->setOption('dir', $config->get('archive-dir'));
        }
        $returnCode = $this->archive($this->getIO(), $config, $input->getArgument('package'), $input->getArgument('version'), $input->getOption('format'), $input->getOption('dir'), $input->getOption('file'), $input->getOption('ignore-filters'), $composer);
        if (0 === $returnCode && $composer) {
            $composer->getEventDispatcher()->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::POST_ARCHIVE_CMD);
        }
        return $returnCode;
    }
    protected function archive(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $packageName = null, $version = null, $format = 'tar', $dest = '.', $fileName = null, $ignoreFilters = \false, \RectorPrefix20210503\Composer\Composer $composer = null)
    {
        if ($composer) {
            $archiveManager = $composer->getArchiveManager();
        } else {
            $factory = new \RectorPrefix20210503\Composer\Factory();
            $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor();
            $httpDownloader = \RectorPrefix20210503\Composer\Factory::createHttpDownloader($io, $config);
            $downloadManager = $factory->createDownloadManager($io, $config, $httpDownloader, $process);
            $archiveManager = $factory->createArchiveManager($config, $downloadManager, new \RectorPrefix20210503\Composer\Util\Loop($httpDownloader, $process));
        }
        if ($packageName) {
            $package = $this->selectPackage($io, $packageName, $version);
            if (!$package) {
                return 1;
            }
        } else {
            $package = $this->getComposer()->getPackage();
        }
        $io->writeError('<info>Creating the archive into "' . $dest . '".</info>');
        $packagePath = $archiveManager->archive($package, $format, $dest, $fileName, $ignoreFilters);
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem();
        $shortPath = $fs->findShortestPath(\getcwd(), $packagePath, \true);
        $io->writeError('Created: ', \false);
        $io->write(\strlen($shortPath) < \strlen($packagePath) ? $shortPath : $packagePath);
        return 0;
    }
    protected function selectPackage(\RectorPrefix20210503\Composer\IO\IOInterface $io, $packageName, $version = null)
    {
        $io->writeError('<info>Searching for the specified package.</info>');
        if ($composer = $this->getComposer(\false)) {
            $localRepo = $composer->getRepositoryManager()->getLocalRepository();
            $repo = new \RectorPrefix20210503\Composer\Repository\CompositeRepository(\array_merge(array($localRepo), $composer->getRepositoryManager()->getRepositories()));
        } else {
            $defaultRepos = \RectorPrefix20210503\Composer\Repository\RepositoryFactory::defaultRepos($this->getIO());
            $io->writeError('No composer.json found in the current directory, searching packages from ' . \implode(', ', \array_keys($defaultRepos)));
            $repo = new \RectorPrefix20210503\Composer\Repository\CompositeRepository($defaultRepos);
        }
        $packages = $repo->findPackages($packageName, $version);
        if (\count($packages) > 1) {
            $package = \reset($packages);
            $io->writeError('<info>Found multiple matches, selected ' . $package->getPrettyString() . '.</info>');
            $io->writeError('Alternatives were ' . \implode(', ', \array_map(function ($p) {
                return $p->getPrettyString();
            }, $packages)) . '.');
            $io->writeError('<comment>Please use a more specific constraint to pick a different package.</comment>');
        } elseif ($packages) {
            $package = \reset($packages);
            $io->writeError('<info>Found an exact match ' . $package->getPrettyString() . '.</info>');
        } else {
            $io->writeError('<error>Could not find a package matching ' . $packageName . '.</error>');
            return \false;
        }
        return $package;
    }
}
