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
namespace RectorPrefix20210503\Composer\Installer;

use RectorPrefix20210503\Composer\Composer;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Installer\InstallationManager;
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\React\Promise\PromiseInterface;
/**
 * Installer for plugin packages
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Nils Adermann <naderman@naderman.de>
 */
class PluginInstaller extends \RectorPrefix20210503\Composer\Installer\LibraryInstaller
{
    /**
     * Initializes Plugin installer.
     *
     * @param IOInterface $io
     * @param Composer    $composer
     */
    public function __construct(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\Util\Filesystem $fs = null, \RectorPrefix20210503\Composer\Installer\BinaryInstaller $binaryInstaller = null)
    {
        parent::__construct($io, $composer, 'composer-plugin', $fs, $binaryInstaller);
    }
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'composer-plugin' || $packageType === 'composer-installer';
    }
    /**
     * {@inheritDoc}
     */
    public function download(\RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $extra = $package->getExtra();
        if (empty($extra['class'])) {
            throw new \UnexpectedValueException('Error while installing ' . $package->getPrettyName() . ', composer-plugin packages should have a class defined in their extra key to be usable.');
        }
        return parent::download($package, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $promise = parent::install($repo, $package);
        if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $pluginManager = $this->composer->getPluginManager();
        $self = $this;
        return $promise->then(function () use($self, $pluginManager, $package, $repo) {
            try {
                \RectorPrefix20210503\Composer\Util\Platform::workaroundFilesystemIssues();
                $pluginManager->registerPackage($package, \true);
            } catch (\Exception $e) {
                $self->rollbackInstall($e, $repo, $package);
            }
        });
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        $promise = parent::update($repo, $initial, $target);
        if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $pluginManager = $this->composer->getPluginManager();
        $self = $this;
        return $promise->then(function () use($self, $pluginManager, $initial, $target, $repo) {
            try {
                \RectorPrefix20210503\Composer\Util\Platform::workaroundFilesystemIssues();
                $pluginManager->deactivatePackage($initial, \true);
                $pluginManager->registerPackage($target, \true);
            } catch (\Exception $e) {
                $self->rollbackInstall($e, $repo, $target);
            }
        });
    }
    public function uninstall(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->composer->getPluginManager()->uninstallPackage($package, \true);
        return parent::uninstall($repo, $package);
    }
    /**
     * TODO v3 should make this private once we can drop PHP 5.3 support
     * @private
     */
    public function rollbackInstall(\Exception $e, \RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->io->writeError('Plugin initialization failed (' . $e->getMessage() . '), uninstalling plugin');
        parent::uninstall($repo, $package);
        throw $e;
    }
}
