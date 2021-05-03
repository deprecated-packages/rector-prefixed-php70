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
/**
 * Does not install anything but marks packages installed in the repo
 *
 * Useful for dry runs
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class NoopInstaller implements \RectorPrefix20210503\Composer\Installer\InstallerInterface
{
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return \true;
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
    }
    /**
     * {@inheritDoc}
     */
    public function prepare($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            $repo->addPackage(clone $package);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        if (!$repo->hasPackage($initial)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $initial);
        }
        $repo->removePackage($initial);
        if (!$repo->hasPackage($target)) {
            $repo->addPackage(clone $target);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $package);
        }
        $repo->removePackage($package);
    }
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $targetDir = $package->getTargetDir();
        return $package->getPrettyName() . ($targetDir ? '/' . $targetDir : '');
    }
}
