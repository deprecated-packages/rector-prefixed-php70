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
use RectorPrefix20210503\Composer\Downloader\ChangeReportInterface;
use RectorPrefix20210503\Composer\Downloader\DvcsDownloaderInterface;
use RectorPrefix20210503\Composer\Downloader\VcsCapableDownloaderInterface;
use RectorPrefix20210503\Composer\Package\Dumper\ArrayDumper;
use RectorPrefix20210503\Composer\Package\Version\VersionGuesser;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\Script\ScriptEvents;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
/**
 * @author Tiago Ribeiro <tiago.ribeiro@seegno.com>
 * @author Rui Marinho <rui.marinho@seegno.com>
 */
class StatusCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    const EXIT_CODE_ERRORS = 1;
    const EXIT_CODE_UNPUSHED_CHANGES = 2;
    const EXIT_CODE_VERSION_CHANGES = 4;
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('status')->setDescription('Shows a list of locally modified packages.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('verbose', 'v|vv|vvv', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Show modified files for each directory that contains changes.')))->setHelp(<<<EOT
The status command displays a list of dependencies that have
been modified locally.

Read more at https://getcomposer.org/doc/03-cli.md#status
EOT
);
    }
    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'status', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        // Dispatch pre-status-command
        $composer->getEventDispatcher()->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::PRE_STATUS_CMD, \true);
        $exitCode = $this->doExecute($input);
        // Dispatch post-status-command
        $composer->getEventDispatcher()->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::POST_STATUS_CMD, \true);
        return $exitCode;
    }
    /**
     * @param  InputInterface $input
     * @return int
     */
    private function doExecute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input)
    {
        // init repos
        $composer = $this->getComposer();
        $installedRepo = $composer->getRepositoryManager()->getLocalRepository();
        $dm = $composer->getDownloadManager();
        $im = $composer->getInstallationManager();
        $errors = array();
        $io = $this->getIO();
        $unpushedChanges = array();
        $vcsVersionChanges = array();
        $parser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
        $guesser = new \RectorPrefix20210503\Composer\Package\Version\VersionGuesser($composer->getConfig(), new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io), $parser);
        $dumper = new \RectorPrefix20210503\Composer\Package\Dumper\ArrayDumper();
        // list packages
        foreach ($installedRepo->getCanonicalPackages() as $package) {
            $downloader = $dm->getDownloaderForPackage($package);
            $targetDir = $im->getInstallPath($package);
            if ($downloader instanceof \RectorPrefix20210503\Composer\Downloader\ChangeReportInterface) {
                if (\is_link($targetDir)) {
                    $errors[$targetDir] = $targetDir . ' is a symbolic link.';
                }
                if ($changes = $downloader->getLocalChanges($package, $targetDir)) {
                    $errors[$targetDir] = $changes;
                }
            }
            if ($downloader instanceof \RectorPrefix20210503\Composer\Downloader\VcsCapableDownloaderInterface) {
                if ($downloader->getVcsReference($package, $targetDir)) {
                    switch ($package->getInstallationSource()) {
                        case 'source':
                            $previousRef = $package->getSourceReference();
                            break;
                        case 'dist':
                            $previousRef = $package->getDistReference();
                            break;
                        default:
                            $previousRef = null;
                    }
                    $currentVersion = $guesser->guessVersion($dumper->dump($package), $targetDir);
                    if ($previousRef && $currentVersion && $currentVersion['commit'] !== $previousRef) {
                        $vcsVersionChanges[$targetDir] = array('previous' => array('version' => $package->getPrettyVersion(), 'ref' => $previousRef), 'current' => array('version' => $currentVersion['pretty_version'], 'ref' => $currentVersion['commit']));
                    }
                }
            }
            if ($downloader instanceof \RectorPrefix20210503\Composer\Downloader\DvcsDownloaderInterface) {
                if ($unpushed = $downloader->getUnpushedChanges($package, $targetDir)) {
                    $unpushedChanges[$targetDir] = $unpushed;
                }
            }
        }
        // output errors/warnings
        if (!$errors && !$unpushedChanges && !$vcsVersionChanges) {
            $io->writeError('<info>No local changes</info>');
            return 0;
        }
        if ($errors) {
            $io->writeError('<error>You have changes in the following dependencies:</error>');
            foreach ($errors as $path => $changes) {
                if ($input->getOption('verbose')) {
                    $indentedChanges = \implode("\n", \array_map(function ($line) {
                        return '    ' . \ltrim($line);
                    }, \explode("\n", $changes)));
                    $io->write('<info>' . $path . '</info>:');
                    $io->write($indentedChanges);
                } else {
                    $io->write($path);
                }
            }
        }
        if ($unpushedChanges) {
            $io->writeError('<warning>You have unpushed changes on the current branch in the following dependencies:</warning>');
            foreach ($unpushedChanges as $path => $changes) {
                if ($input->getOption('verbose')) {
                    $indentedChanges = \implode("\n", \array_map(function ($line) {
                        return '    ' . \ltrim($line);
                    }, \explode("\n", $changes)));
                    $io->write('<info>' . $path . '</info>:');
                    $io->write($indentedChanges);
                } else {
                    $io->write($path);
                }
            }
        }
        if ($vcsVersionChanges) {
            $io->writeError('<warning>You have version variations in the following dependencies:</warning>');
            foreach ($vcsVersionChanges as $path => $changes) {
                if ($input->getOption('verbose')) {
                    // If we don't can't find a version, use the ref instead.
                    $currentVersion = $changes['current']['version'] ?: $changes['current']['ref'];
                    $previousVersion = $changes['previous']['version'] ?: $changes['previous']['ref'];
                    if ($io->isVeryVerbose()) {
                        // Output the ref regardless of whether or not it's being used as the version
                        $currentVersion .= \sprintf(' (%s)', $changes['current']['ref']);
                        $previousVersion .= \sprintf(' (%s)', $changes['previous']['ref']);
                    }
                    $io->write('<info>' . $path . '</info>:');
                    $io->write(\sprintf('    From <comment>%s</comment> to <comment>%s</comment>', $previousVersion, $currentVersion));
                } else {
                    $io->write($path);
                }
            }
        }
        if (($errors || $unpushedChanges || $vcsVersionChanges) && !$input->getOption('verbose')) {
            $io->writeError('Use --verbose (-v) to see a list of files');
        }
        return ($errors ? self::EXIT_CODE_ERRORS : 0) + ($unpushedChanges ? self::EXIT_CODE_UNPUSHED_CHANGES : 0) + ($vcsVersionChanges ? self::EXIT_CODE_VERSION_CHANGES : 0);
    }
}
