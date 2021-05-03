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
namespace RectorPrefix20210503\Composer\Package;

use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\Installer\InstallationManager;
use RectorPrefix20210503\Composer\Repository\LockArrayRepository;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Package\Dumper\ArrayDumper;
use RectorPrefix20210503\Composer\Package\Loader\ArrayLoader;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Plugin\PluginInterface;
use RectorPrefix20210503\Composer\Util\Git as GitUtil;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Seld\JsonLint\ParsingException;
/**
 * Reads/writes project lockfile (composer.lock).
 *
 * @author Konstantin Kudryashiv <ever.zet@gmail.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Locker
{
    /** @var JsonFile */
    private $lockFile;
    /** @var InstallationManager */
    private $installationManager;
    /** @var string */
    private $hash;
    /** @var string */
    private $contentHash;
    /** @var ArrayLoader */
    private $loader;
    /** @var ArrayDumper */
    private $dumper;
    /** @var ProcessExecutor */
    private $process;
    private $lockDataCache;
    private $virtualFileWritten;
    /**
     * Initializes packages locker.
     *
     * @param IOInterface         $io
     * @param JsonFile            $lockFile             lockfile loader
     * @param InstallationManager $installationManager  installation manager instance
     * @param string              $composerFileContents The contents of the composer file
     */
    public function __construct(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Json\JsonFile $lockFile, \RectorPrefix20210503\Composer\Installer\InstallationManager $installationManager, $composerFileContents, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process = null)
    {
        $this->lockFile = $lockFile;
        $this->installationManager = $installationManager;
        $this->hash = \md5($composerFileContents);
        $this->contentHash = self::getContentHash($composerFileContents);
        $this->loader = new \RectorPrefix20210503\Composer\Package\Loader\ArrayLoader(null, \true);
        $this->dumper = new \RectorPrefix20210503\Composer\Package\Dumper\ArrayDumper();
        $this->process = $process ?: new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
    }
    /**
     * Returns the md5 hash of the sorted content of the composer file.
     *
     * @param string $composerFileContents The contents of the composer file.
     *
     * @return string
     */
    public static function getContentHash($composerFileContents)
    {
        $content = \json_decode($composerFileContents, \true);
        $relevantKeys = array('name', 'version', 'require', 'require-dev', 'conflict', 'replace', 'provide', 'minimum-stability', 'prefer-stable', 'repositories', 'extra');
        $relevantContent = array();
        foreach (\array_intersect($relevantKeys, \array_keys($content)) as $key) {
            $relevantContent[$key] = $content[$key];
        }
        if (isset($content['config']['platform'])) {
            $relevantContent['config']['platform'] = $content['config']['platform'];
        }
        \ksort($relevantContent);
        return \md5(\json_encode($relevantContent));
    }
    /**
     * Checks whether locker has been locked (lockfile found).
     *
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->virtualFileWritten && !$this->lockFile->exists()) {
            return \false;
        }
        $data = $this->getLockData();
        return isset($data['packages']);
    }
    /**
     * Checks whether the lock file is still up to date with the current hash
     *
     * @return bool
     */
    public function isFresh()
    {
        $lock = $this->lockFile->read();
        if (!empty($lock['content-hash'])) {
            // There is a content hash key, use that instead of the file hash
            return $this->contentHash === $lock['content-hash'];
        }
        // BC support for old lock files without content-hash
        if (!empty($lock['hash'])) {
            return $this->hash === $lock['hash'];
        }
        // should not be reached unless the lock file is corrupted, so assume it's out of date
        return \false;
    }
    /**
     * Searches and returns an array of locked packages, retrieved from registered repositories.
     *
     * @param  bool                                     $withDevReqs true to retrieve the locked dev packages
     * @throws \RuntimeException
     * @return \Composer\Repository\LockArrayRepository
     */
    public function getLockedRepository($withDevReqs = \false)
    {
        $lockData = $this->getLockData();
        $packages = new \RectorPrefix20210503\Composer\Repository\LockArrayRepository();
        $lockedPackages = $lockData['packages'];
        if ($withDevReqs) {
            if (isset($lockData['packages-dev'])) {
                $lockedPackages = \array_merge($lockedPackages, $lockData['packages-dev']);
            } else {
                throw new \RuntimeException('The lock file does not contain require-dev information, run install with the --no-dev option or delete it and run composer update to generate a new lock file.');
            }
        }
        if (empty($lockedPackages)) {
            return $packages;
        }
        if (isset($lockedPackages[0]['name'])) {
            $packageByName = array();
            foreach ($lockedPackages as $info) {
                $package = $this->loader->load($info);
                $packages->addPackage($package);
                $packageByName[$package->getName()] = $package;
                if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage) {
                    $packageByName[$package->getAliasOf()->getName()] = $package->getAliasOf();
                }
            }
            if (isset($lockData['aliases'])) {
                foreach ($lockData['aliases'] as $alias) {
                    if (isset($packageByName[$alias['package']])) {
                        $aliasPkg = new \RectorPrefix20210503\Composer\Package\AliasPackage($packageByName[$alias['package']], $alias['alias_normalized'], $alias['alias']);
                        $aliasPkg->setRootPackageAlias(\true);
                        $packages->addPackage($aliasPkg);
                    }
                }
            }
            return $packages;
        }
        throw new \RuntimeException('Your composer.lock is invalid. Run "composer update" to generate a new one.');
    }
    /**
     * @return string[] Names of dependencies installed through require-dev
     */
    public function getDevPackageNames()
    {
        $names = array();
        $lockData = $this->getLockData();
        if (isset($lockData['packages-dev'])) {
            foreach ($lockData['packages-dev'] as $package) {
                $names[] = \strtolower($package['name']);
            }
        }
        return $names;
    }
    /**
     * Returns the platform requirements stored in the lock file
     *
     * @param  bool                     $withDevReqs if true, the platform requirements from the require-dev block are also returned
     * @return \Composer\Package\Link[]
     */
    public function getPlatformRequirements($withDevReqs = \false)
    {
        $lockData = $this->getLockData();
        $requirements = array();
        if (!empty($lockData['platform'])) {
            $requirements = $this->loader->parseLinks('__root__', '1.0.0', \RectorPrefix20210503\Composer\Package\Link::TYPE_REQUIRE, isset($lockData['platform']) ? $lockData['platform'] : array());
        }
        if ($withDevReqs && !empty($lockData['platform-dev'])) {
            $devRequirements = $this->loader->parseLinks('__root__', '1.0.0', \RectorPrefix20210503\Composer\Package\Link::TYPE_REQUIRE, isset($lockData['platform-dev']) ? $lockData['platform-dev'] : array());
            $requirements = \array_merge($requirements, $devRequirements);
        }
        return $requirements;
    }
    public function getMinimumStability()
    {
        $lockData = $this->getLockData();
        return isset($lockData['minimum-stability']) ? $lockData['minimum-stability'] : 'stable';
    }
    public function getStabilityFlags()
    {
        $lockData = $this->getLockData();
        return isset($lockData['stability-flags']) ? $lockData['stability-flags'] : array();
    }
    public function getPreferStable()
    {
        $lockData = $this->getLockData();
        // return null if not set to allow caller logic to choose the
        // right behavior since old lock files have no prefer-stable
        return isset($lockData['prefer-stable']) ? $lockData['prefer-stable'] : null;
    }
    public function getPreferLowest()
    {
        $lockData = $this->getLockData();
        // return null if not set to allow caller logic to choose the
        // right behavior since old lock files have no prefer-lowest
        return isset($lockData['prefer-lowest']) ? $lockData['prefer-lowest'] : null;
    }
    public function getPlatformOverrides()
    {
        $lockData = $this->getLockData();
        return isset($lockData['platform-overrides']) ? $lockData['platform-overrides'] : array();
    }
    public function getAliases()
    {
        $lockData = $this->getLockData();
        return isset($lockData['aliases']) ? $lockData['aliases'] : array();
    }
    public function getLockData()
    {
        if (null !== $this->lockDataCache) {
            return $this->lockDataCache;
        }
        if (!$this->lockFile->exists()) {
            throw new \LogicException('No lockfile found. Unable to read locked packages');
        }
        return $this->lockDataCache = $this->lockFile->read();
    }
    /**
     * Locks provided data into lockfile.
     *
     * @param array  $packages          array of packages
     * @param mixed  $devPackages       array of dev packages or null if installed without --dev
     * @param array  $platformReqs      array of package name => constraint for required platform packages
     * @param mixed  $platformDevReqs   array of package name => constraint for dev-required platform packages
     * @param array  $aliases           array of aliases
     * @param string $minimumStability
     * @param array  $stabilityFlags
     * @param bool   $preferStable
     * @param bool   $preferLowest
     * @param array  $platformOverrides
     * @param bool   $write             Whether to actually write data to disk, useful in tests and for --dry-run
     *
     * @return bool
     */
    public function setLockData(array $packages, $devPackages, array $platformReqs, $platformDevReqs, array $aliases, $minimumStability, array $stabilityFlags, $preferStable, $preferLowest, array $platformOverrides, $write = \true)
    {
        // keep old default branch names normalized to DEFAULT_BRANCH_ALIAS for BC as that is how Composer 1 outputs the lock file
        // when loading the lock file the version is anyway ignored in Composer 2, so it has no adverse effect
        $aliases = \array_map(function ($alias) {
            if (\in_array($alias['version'], array('dev-master', 'dev-trunk', 'dev-default'), \true)) {
                $alias['version'] = \RectorPrefix20210503\Composer\Package\Version\VersionParser::DEFAULT_BRANCH_ALIAS;
            }
            return $alias;
        }, $aliases);
        $lock = array('_readme' => array('This file locks the dependencies of your project to a known state', 'Read more about it at https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies', 'This file is @gener' . 'ated automatically'), 'content-hash' => $this->contentHash, 'packages' => null, 'packages-dev' => null, 'aliases' => $aliases, 'minimum-stability' => $minimumStability, 'stability-flags' => $stabilityFlags, 'prefer-stable' => $preferStable, 'prefer-lowest' => $preferLowest);
        $lock['packages'] = $this->lockPackages($packages);
        if (null !== $devPackages) {
            $lock['packages-dev'] = $this->lockPackages($devPackages);
        }
        $lock['platform'] = $platformReqs;
        $lock['platform-dev'] = $platformDevReqs;
        if ($platformOverrides) {
            $lock['platform-overrides'] = $platformOverrides;
        }
        $lock['plugin-api-version'] = \RectorPrefix20210503\Composer\Plugin\PluginInterface::PLUGIN_API_VERSION;
        try {
            $isLocked = $this->isLocked();
        } catch (\RectorPrefix20210503\Seld\JsonLint\ParsingException $e) {
            $isLocked = \false;
        }
        if (!$isLocked || $lock !== $this->getLockData()) {
            if ($write) {
                $this->lockFile->write($lock);
                $this->lockDataCache = null;
                $this->virtualFileWritten = \false;
            } else {
                $this->virtualFileWritten = \true;
                $this->lockDataCache = \RectorPrefix20210503\Composer\Json\JsonFile::parseJson(\RectorPrefix20210503\Composer\Json\JsonFile::encode($lock, 448 & \RectorPrefix20210503\Composer\Json\JsonFile::JSON_PRETTY_PRINT));
            }
            return \true;
        }
        return \false;
    }
    private function lockPackages(array $packages)
    {
        $locked = array();
        foreach ($packages as $package) {
            if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage) {
                continue;
            }
            $name = $package->getPrettyName();
            $version = $package->getPrettyVersion();
            if (!$name || !$version) {
                throw new \LogicException(\sprintf('Package "%s" has no version or name and can not be locked', $package));
            }
            $spec = $this->dumper->dump($package);
            unset($spec['version_normalized']);
            // always move time to the end of the package definition
            $time = isset($spec['time']) ? $spec['time'] : null;
            unset($spec['time']);
            if ($package->isDev() && $package->getInstallationSource() === 'source') {
                // use the exact commit time of the current reference if it's a dev package
                $time = $this->getPackageTime($package) ?: $time;
            }
            if (null !== $time) {
                $spec['time'] = $time;
            }
            unset($spec['installation-source']);
            $locked[] = $spec;
        }
        \usort($locked, function ($a, $b) {
            $comparison = \strcmp($a['name'], $b['name']);
            if (0 !== $comparison) {
                return $comparison;
            }
            // If it is the same package, compare the versions to make the order deterministic
            return \strcmp($a['version'], $b['version']);
        });
        return $locked;
    }
    /**
     * Returns the packages's datetime for its source reference.
     *
     * @param  PackageInterface $package The package to scan.
     * @return string|null      The formatted datetime or null if none was found.
     */
    private function getPackageTime(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if (!\function_exists('proc_open')) {
            return null;
        }
        $path = \realpath($this->installationManager->getInstallPath($package));
        $sourceType = $package->getSourceType();
        $datetime = null;
        if ($path && \in_array($sourceType, array('git', 'hg'))) {
            $sourceRef = $package->getSourceReference() ?: $package->getDistReference();
            switch ($sourceType) {
                case 'git':
                    \RectorPrefix20210503\Composer\Util\Git::cleanEnv();
                    if (0 === $this->process->execute('git log -n1 --pretty=%ct ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($sourceRef) . \RectorPrefix20210503\Composer\Util\Git::getNoShowSignatureFlag($this->process), $output, $path) && \preg_match('{^\\s*\\d+\\s*$}', $output)) {
                        $datetime = new \DateTime('@' . \trim($output), new \DateTimeZone('UTC'));
                    }
                    break;
                case 'hg':
                    if (0 === $this->process->execute('hg log --template "{date|hgdate}" -r ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($sourceRef), $output, $path) && \preg_match('{^\\s*(\\d+)\\s*}', $output, $match)) {
                        $datetime = new \DateTime('@' . $match[1], new \DateTimeZone('UTC'));
                    }
                    break;
            }
        }
        return $datetime ? $datetime->format(\DATE_RFC3339) : null;
    }
}
