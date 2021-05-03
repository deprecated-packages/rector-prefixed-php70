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

use RectorPrefix20210503\Composer\Repository\PlatformRepository;
use RectorPrefix20210503\Composer\Repository\RootPackageRepository;
use RectorPrefix20210503\Composer\Repository\InstalledRepository;
use RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
class SuggestsCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('suggests')->setDescription('Shows package suggestions.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('by-package', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Groups output by suggesting package (default)'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('by-suggestion', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Groups output by suggested package'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('all', 'a', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Show suggestions from all dependencies, including transitive ones'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('list', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Show only list of suggested package names'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Exclude suggestions from require-dev packages'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('packages', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Packages that you want to list suggestions from.')))->setHelp(<<<EOT

The <info>%command.name%</info> command shows a sorted list of suggested packages.

Read more at https://getcomposer.org/doc/03-cli.md#suggests
EOT
);
    }
    /**
     * {@inheritDoc}
     */
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $installedRepos = array(new \RectorPrefix20210503\Composer\Repository\RootPackageRepository(clone $composer->getPackage()));
        $locker = $composer->getLocker();
        if ($locker->isLocked()) {
            $installedRepos[] = new \RectorPrefix20210503\Composer\Repository\PlatformRepository(array(), $locker->getPlatformOverrides());
            $installedRepos[] = $locker->getLockedRepository(!$input->getOption('no-dev'));
        } else {
            $installedRepos[] = new \RectorPrefix20210503\Composer\Repository\PlatformRepository(array(), $composer->getConfig()->get('platform') ?: array());
            $installedRepos[] = $composer->getRepositoryManager()->getLocalRepository();
        }
        $installedRepo = new \RectorPrefix20210503\Composer\Repository\InstalledRepository($installedRepos);
        $reporter = new \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter($this->getIO());
        $filter = $input->getArgument('packages');
        $packages = $installedRepo->getPackages();
        $packages[] = $composer->getPackage();
        foreach ($packages as $package) {
            if (!empty($filter) && !\in_array($package->getName(), $filter)) {
                continue;
            }
            $reporter->addSuggestionsFromPackage($package);
        }
        // Determine output mode, default is by-package
        $mode = \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter::MODE_BY_PACKAGE;
        // if by-suggestion is given we override the default
        if ($input->getOption('by-suggestion')) {
            $mode = \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter::MODE_BY_SUGGESTION;
        }
        // unless by-package is also present then we enable both
        if ($input->getOption('by-package')) {
            $mode |= \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter::MODE_BY_PACKAGE;
        }
        // list is exclusive and overrides everything else
        if ($input->getOption('list')) {
            $mode = \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter::MODE_LIST;
        }
        $reporter->output($mode, $installedRepo, empty($filter) && !$input->getOption('all') ? $composer->getPackage() : null);
        return 0;
    }
}
