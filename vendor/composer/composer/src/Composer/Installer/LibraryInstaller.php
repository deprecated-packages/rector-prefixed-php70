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
use RectorPrefix20210503\Composer\Util\Silencer;
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\React\Promise\PromiseInterface;
/**
 * Package installation manager.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LibraryInstaller implements \RectorPrefix20210503\Composer\Installer\InstallerInterface, \RectorPrefix20210503\Composer\Installer\BinaryPresenceInterface
{
    protected $composer;
    protected $vendorDir;
    protected $binDir;
    protected $downloadManager;
    protected $io;
    protected $type;
    protected $filesystem;
    protected $binCompat;
    protected $binaryInstaller;
    /**
     * Initializes library installer.
     *
     * @param IOInterface     $io
     * @param Composer        $composer
     * @param string|null     $type
     * @param Filesystem      $filesystem
     * @param BinaryInstaller $binaryInstaller
     */
    public function __construct(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Composer $composer, $type = 'library', \RectorPrefix20210503\Composer\Util\Filesystem $filesystem = null, \RectorPrefix20210503\Composer\Installer\BinaryInstaller $binaryInstaller = null)
    {
        $this->composer = $composer;
        $this->downloadManager = $composer->getDownloadManager();
        $this->io = $io;
        $this->type = $type;
        $this->filesystem = $filesystem ?: new \RectorPrefix20210503\Composer\Util\Filesystem();
        $this->vendorDir = \rtrim($composer->getConfig()->get('vendor-dir'), '/');
        $this->binaryInstaller = $binaryInstaller ?: new \RectorPrefix20210503\Composer\Installer\BinaryInstaller($this->io, \rtrim($composer->getConfig()->get('bin-dir'), '/'), $composer->getConfig()->get('bin-compat'), $this->filesystem);
    }
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === $this->type || null === $this->type;
    }
    /**
     * {@inheritDoc}
     */
    public function isInstalled(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            return \false;
        }
        $installPath = $this->getInstallPath($package);
        if (\is_readable($installPath)) {
            return \true;
        }
        return \RectorPrefix20210503\Composer\Util\Platform::isWindows() && $this->filesystem->isJunction($installPath) || \is_link($installPath);
    }
    /**
     * {@inheritDoc}
     */
    public function download(\RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);
        return $this->downloadManager->download($package, $downloadPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function prepare($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);
        return $this->downloadManager->prepare($type, $package, $downloadPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);
        return $this->downloadManager->cleanup($type, $package, $downloadPath, $prevPackage);
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);
        // remove the binaries if it appears the package files are missing
        if (!\is_readable($downloadPath) && $repo->hasPackage($package)) {
            $this->binaryInstaller->removeBinaries($package);
        }
        $promise = $this->installCode($package);
        if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $binaryInstaller = $this->binaryInstaller;
        $installPath = $this->getInstallPath($package);
        return $promise->then(function () use($binaryInstaller, $installPath, $package, $repo) {
            $binaryInstaller->installBinaries($package, $installPath);
            if (!$repo->hasPackage($package)) {
                $repo->addPackage(clone $package);
            }
        });
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        if (!$repo->hasPackage($initial)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $initial);
        }
        $this->initializeVendorDir();
        $this->binaryInstaller->removeBinaries($initial);
        $promise = $this->updateCode($initial, $target);
        if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $binaryInstaller = $this->binaryInstaller;
        $installPath = $this->getInstallPath($target);
        return $promise->then(function () use($binaryInstaller, $installPath, $target, $initial, $repo) {
            $binaryInstaller->installBinaries($target, $installPath);
            $repo->removePackage($initial);
            if (!$repo->hasPackage($target)) {
                $repo->addPackage(clone $target);
            }
        });
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface $repo, \RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: ' . $package);
        }
        $promise = $this->removeCode($package);
        if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $binaryInstaller = $this->binaryInstaller;
        $downloadPath = $this->getPackageBasePath($package);
        $filesystem = $this->filesystem;
        return $promise->then(function () use($binaryInstaller, $filesystem, $downloadPath, $package, $repo) {
            $binaryInstaller->removeBinaries($package);
            $repo->removePackage($package);
            if (\strpos($package->getName(), '/')) {
                $packageVendorDir = \dirname($downloadPath);
                if (\is_dir($packageVendorDir) && $filesystem->isDirEmpty($packageVendorDir)) {
                    \RectorPrefix20210503\Composer\Util\Silencer::call('rmdir', $packageVendorDir);
                }
            }
        });
    }
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->initializeVendorDir();
        $basePath = ($this->vendorDir ? $this->vendorDir . '/' : '') . $package->getPrettyName();
        $targetDir = $package->getTargetDir();
        return $basePath . ($targetDir ? '/' . $targetDir : '');
    }
    /**
     * Make sure binaries are installed for a given package.
     *
     * @param PackageInterface $package Package instance
     */
    public function ensureBinariesPresence(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->binaryInstaller->installBinaries($package, $this->getInstallPath($package), \false);
    }
    /**
     * Returns the base path of the package without target-dir path
     *
     * It is used for BC as getInstallPath tends to be overridden by
     * installer plugins but not getPackageBasePath
     *
     * @param  PackageInterface $package
     * @return string
     */
    protected function getPackageBasePath(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $installPath = $this->getInstallPath($package);
        $targetDir = $package->getTargetDir();
        if ($targetDir) {
            return \preg_replace('{/*' . \str_replace('/', '/+', \preg_quote($targetDir)) . '/?$}', '', $installPath);
        }
        return $installPath;
    }
    protected function installCode(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $downloadPath = $this->getInstallPath($package);
        return $this->downloadManager->install($package, $downloadPath);
    }
    protected function updateCode(\RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        $initialDownloadPath = $this->getInstallPath($initial);
        $targetDownloadPath = $this->getInstallPath($target);
        if ($targetDownloadPath !== $initialDownloadPath) {
            // if the target and initial dirs intersect, we force a remove + install
            // to avoid the rename wiping the target dir as part of the initial dir cleanup
            if (\strpos($initialDownloadPath, $targetDownloadPath) === 0 || \strpos($targetDownloadPath, $initialDownloadPath) === 0) {
                $promise = $this->removeCode($initial);
                if (!$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
                    $promise = \RectorPrefix20210503\React\Promise\resolve();
                }
                $self = $this;
                return $promise->then(function () use($self, $target) {
                    $reflMethod = new \ReflectionMethod($self, 'installCode');
                    $reflMethod->setAccessible(\true);
                    // equivalent of $this->installCode($target) with php 5.3 support
                    // TODO remove this once 5.3 support is dropped
                    return $reflMethod->invoke($self, $target);
                });
            }
            $this->filesystem->rename($initialDownloadPath, $targetDownloadPath);
        }
        return $this->downloadManager->update($initial, $target, $targetDownloadPath);
    }
    protected function removeCode(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $downloadPath = $this->getPackageBasePath($package);
        return $this->downloadManager->remove($package, $downloadPath);
    }
    protected function initializeVendorDir()
    {
        $this->filesystem->ensureDirectoryExists($this->vendorDir);
        $this->vendorDir = \realpath($this->vendorDir);
    }
}
