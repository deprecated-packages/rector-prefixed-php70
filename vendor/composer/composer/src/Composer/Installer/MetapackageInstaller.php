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

use RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\UpdateOperation;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\UninstallOperation;
/**
 * Metapackage installation manager.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class MetapackageInstaller implements \RectorPrefix20210503\Composer\Installer\InstallerInterface
{
    private $io;
    public function __construct(\RectorPrefix20210503\Composer\IO\IOInterface $io)
    {
        $this->io = $io;
    }
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'metapackage';
    }
    /**
     * {@inheritDoc}
     */
    public function isInstalled(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        return $repo->hasPackage($package);
    }
    /**
     * {@inheritDoc}
     */
    public function download(\RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        // noop
    }
    /**
     * {@inheritDoc}
     */
    public function prepare($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        // noop
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        // noop
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation::format($package));
        $repo->addPackage(clone $package);
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        if (!$repo->hasPackage($initial)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $initial);
        }
        $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\UpdateOperation::format($initial, $target));
        $repo->removePackage($initial);
        $repo->addPackage(clone $target);
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $package);
        }
        $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\UninstallOperation::format($package));
        $repo->removePackage($package);
    }
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        return '';
    }
}
