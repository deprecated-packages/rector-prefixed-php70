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

use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Downloader\DownloadManager;
use RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface;
use RectorPrefix20210503\Composer\Util\Filesystem;
/**
 * Project Installer is used to install a single package into a directory as
 * root project.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ProjectInstaller implements \RectorPrefix20210503\Composer\Installer\InstallerInterface
{
    private $installPath;
    private $downloadManager;
    private $filesystem;
    public function __construct($installPath, \RectorPrefix20210503\Composer\Downloader\DownloadManager $dm, \RectorPrefix20210503\Composer\Util\Filesystem $fs)
    {
        $this->installPath = \rtrim(\strtr($installPath, '\\', '/'), '/') . '/';
        $this->downloadManager = $dm;
        $this->filesystem = $fs;
    }
    /**
     * Decides if the installer supports the given type
     *
     * @param  string $packageType
     * @return bool
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
        return \false;
    }
    /**
     * {@inheritDoc}
     */
    public function download(\RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $installPath = $this->installPath;
        if (\file_exists($installPath) && !$this->filesystem->isDirEmpty($installPath)) {
            throw new \InvalidArgumentException("Project directory {$installPath} is not empty.");
        }
        if (!\is_dir($installPath)) {
            \mkdir($installPath, 0777, \true);
        }
        return $this->downloadManager->download($package, $installPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function prepare($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        return $this->downloadManager->prepare($type, $package, $this->installPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        return $this->downloadManager->cleanup($type, $package, $this->installPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        return $this->downloadManager->install($package, $this->installPath);
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        throw new \InvalidArgumentException("not supported");
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        throw new \InvalidArgumentException("not supported");
    }
    /**
     * Returns the installation path of a package
     *
     * @param  PackageInterface $package
     * @return string           path
     */
    public function getInstallPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        return $this->installPath;
    }
}
