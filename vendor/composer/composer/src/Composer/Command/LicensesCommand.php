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

use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Repository\RepositoryInterface;
use RectorPrefix20210503\Symfony\Component\Console\Helper\Table;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @author Benoît Merlet <benoit.merlet@gmail.com>
 */
class LicensesCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('licenses')->setDescription('Shows information about licenses of dependencies.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('format', 'f', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Format of the output: text, json or summary', 'text'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables search in require-dev packages.')))->setHelp(<<<EOT
The license command displays detailed information about the licenses of
the installed dependencies.

Read more at https://getcomposer.org/doc/03-cli.md#licenses
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'licenses', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $root = $composer->getPackage();
        $repo = $composer->getRepositoryManager()->getLocalRepository();
        if ($input->getOption('no-dev')) {
            $packages = $this->filterRequiredPackages($repo, $root);
        } else {
            $packages = $this->appendPackages($repo->getPackages(), array());
        }
        \ksort($packages);
        $io = $this->getIO();
        switch ($format = $input->getOption('format')) {
            case 'text':
                $io->write('Name: <comment>' . $root->getPrettyName() . '</comment>');
                $io->write('Version: <comment>' . $root->getFullPrettyVersion() . '</comment>');
                $io->write('Licenses: <comment>' . (\implode(', ', $root->getLicense()) ?: 'none') . '</comment>');
                $io->write('Dependencies:');
                $io->write('');
                $table = new \RectorPrefix20210503\Symfony\Component\Console\Helper\Table($output);
                $table->setStyle('compact');
                $tableStyle = $table->getStyle();
                if (\method_exists($tableStyle, 'setVerticalBorderChars')) {
                    $tableStyle->setVerticalBorderChars('');
                } else {
                    $tableStyle->setVerticalBorderChar('');
                }
                $tableStyle->setCellRowContentFormat('%s  ');
                $table->setHeaders(array('Name', 'Version', 'License'));
                foreach ($packages as $package) {
                    $table->addRow(array($package->getPrettyName(), $package->getFullPrettyVersion(), \implode(', ', $package->getLicense()) ?: 'none'));
                }
                $table->render();
                break;
            case 'json':
                $dependencies = array();
                foreach ($packages as $package) {
                    $dependencies[$package->getPrettyName()] = array('version' => $package->getFullPrettyVersion(), 'license' => $package->getLicense());
                }
                $io->write(\RectorPrefix20210503\Composer\Json\JsonFile::encode(array('name' => $root->getPrettyName(), 'version' => $root->getFullPrettyVersion(), 'license' => $root->getLicense(), 'dependencies' => $dependencies)));
                break;
            case 'summary':
                $usedLicenses = array();
                foreach ($packages as $package) {
                    $license = $package->getLicense();
                    $licenseName = $license[0];
                    if (!isset($usedLicenses[$licenseName])) {
                        $usedLicenses[$licenseName] = 0;
                    }
                    $usedLicenses[$licenseName]++;
                }
                // Sort licenses so that the most used license will appear first
                \arsort($usedLicenses, \SORT_NUMERIC);
                $rows = array();
                foreach ($usedLicenses as $usedLicense => $numberOfDependencies) {
                    $rows[] = array($usedLicense, $numberOfDependencies);
                }
                $symfonyIo = new \RectorPrefix20210503\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
                $symfonyIo->table(array('License', 'Number of dependencies'), $rows);
                break;
            default:
                throw new \RuntimeException(\sprintf('Unsupported format "%s".  See help for supported formats.', $format));
        }
        return 0;
    }
    /**
     * Find package requires and child requires
     *
     * @param  RepositoryInterface $repo
     * @param  PackageInterface    $package
     * @param  array               $bucket
     * @return array
     */
    private function filterRequiredPackages(\RectorPrefix20210503\Composer\Repository\RepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package, $bucket = array())
    {
        $requires = \array_keys($package->getRequires());
        $packageListNames = \array_keys($bucket);
        $packages = \array_filter($repo->getPackages(), function ($package) use($requires, $packageListNames) {
            return \in_array($package->getName(), $requires) && !\in_array($package->getName(), $packageListNames);
        });
        $bucket = $this->appendPackages($packages, $bucket);
        foreach ($packages as $package) {
            $bucket = $this->filterRequiredPackages($repo, $package, $bucket);
        }
        return $bucket;
    }
    /**
     * Adds packages to the package list
     *
     * @param  array $packages the list of packages to add
     * @param  array $bucket   the list to add packages to
     * @return array
     */
    public function appendPackages(array $packages, array $bucket)
    {
        foreach ($packages as $package) {
            $bucket[$package->getName()] = $package;
        }
        return $bucket;
    }
}
