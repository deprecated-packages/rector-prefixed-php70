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

use RectorPrefix20210503\Composer\Package\CompletePackageInterface;
use RectorPrefix20210503\Composer\Repository\RepositoryInterface;
use RectorPrefix20210503\Composer\Repository\RootPackageRepository;
use RectorPrefix20210503\Composer\Repository\RepositoryFactory;
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class HomeCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('browse')->setAliases(array('home'))->setDescription('Opens the package\'s repository URL or homepage in your browser.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('packages', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Package(s) to browse to.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('homepage', 'H', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Open the homepage instead of the repository URL.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('show', 's', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Only show the homepage or repository URL.')))->setHelp(<<<EOT
The home command opens or shows a package's repository URL or
homepage in your default browser.

To open the homepage by default, use -H or --homepage.
To show instead of open the repository or homepage URL, use -s or --show.

Read more at https://getcomposer.org/doc/03-cli.md#browse-home
EOT
);
    }
    /**
     * {@inheritDoc}
     */
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $repos = $this->initializeRepos();
        $io = $this->getIO();
        $return = 0;
        $packages = $input->getArgument('packages');
        if (!$packages) {
            $io->writeError('No package specified, opening homepage for the root package');
            $packages = array($this->getComposer()->getPackage()->getName());
        }
        foreach ($packages as $packageName) {
            $handled = \false;
            $packageExists = \false;
            foreach ($repos as $repo) {
                foreach ($repo->findPackages($packageName) as $package) {
                    $packageExists = \true;
                    if ($package instanceof \RectorPrefix20210503\Composer\Package\CompletePackageInterface && $this->handlePackage($package, $input->getOption('homepage'), $input->getOption('show'))) {
                        $handled = \true;
                        break 2;
                    }
                }
            }
            if (!$packageExists) {
                $return = 1;
                $io->writeError('<warning>Package ' . $packageName . ' not found</warning>');
            }
            if (!$handled) {
                $return = 1;
                $io->writeError('<warning>' . ($input->getOption('homepage') ? 'Invalid or missing homepage' : 'Invalid or missing repository URL') . ' for ' . $packageName . '</warning>');
            }
        }
        return $return;
    }
    private function handlePackage(\RectorPrefix20210503\Composer\Package\CompletePackageInterface $package, $showHomepage, $showOnly)
    {
        $support = $package->getSupport();
        $url = isset($support['source']) ? $support['source'] : $package->getSourceUrl();
        if (!$url || $showHomepage) {
            $url = $package->getHomepage();
        }
        if (!$url || !\filter_var($url, \FILTER_VALIDATE_URL)) {
            return \false;
        }
        if ($showOnly) {
            $this->getIO()->write(\sprintf('<info>%s</info>', $url));
        } else {
            $this->openBrowser($url);
        }
        return \true;
    }
    /**
     * opens a url in your system default browser
     *
     * @param string $url
     */
    private function openBrowser($url)
    {
        $url = \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($url);
        $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor($this->getIO());
        if (\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            return $process->execute('start "web" explorer "' . $url . '"', $output);
        }
        $linux = $process->execute('which xdg-open', $output);
        $osx = $process->execute('which open', $output);
        if (0 === $linux) {
            $process->execute('xdg-open ' . $url, $output);
        } elseif (0 === $osx) {
            $process->execute('open ' . $url, $output);
        } else {
            $this->getIO()->writeError('No suitable browser opening command found, open yourself: ' . $url);
        }
    }
    /**
     * Initializes repositories
     *
     * Returns an array of repos in order they should be checked in
     *
     * @return RepositoryInterface[]
     */
    private function initializeRepos()
    {
        $composer = $this->getComposer(\false);
        if ($composer) {
            return \array_merge(
                array(new \RectorPrefix20210503\Composer\Repository\RootPackageRepository($composer->getPackage())),
                // root package
                array($composer->getRepositoryManager()->getLocalRepository()),
                // installed packages
                $composer->getRepositoryManager()->getRepositories()
            );
        }
        return \RectorPrefix20210503\Composer\Repository\RepositoryFactory::defaultRepos($this->getIO());
    }
}
