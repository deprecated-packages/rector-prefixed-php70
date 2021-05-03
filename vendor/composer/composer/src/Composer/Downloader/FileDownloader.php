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
namespace RectorPrefix20210503\Composer\Downloader;

use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\Cache;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\IO\NullIO;
use RectorPrefix20210503\Composer\Exception\IrrecoverableDownloadException;
use RectorPrefix20210503\Composer\Package\Comparer\Comparer;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\UpdateOperation;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\UninstallOperation;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\Plugin\PostFileDownloadEvent;
use RectorPrefix20210503\Composer\Plugin\PreFileDownloadEvent;
use RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Util\Silencer;
use RectorPrefix20210503\Composer\Util\HttpDownloader;
use RectorPrefix20210503\Composer\Util\Url as UrlUtil;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\React\Promise\PromiseInterface;
/**
 * Base downloader for files
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Nils Adermann <naderman@naderman.de>
 */
class FileDownloader implements \RectorPrefix20210503\Composer\Downloader\DownloaderInterface, \RectorPrefix20210503\Composer\Downloader\ChangeReportInterface
{
    /** @var IOInterface */
    protected $io;
    /** @var Config */
    protected $config;
    /** @var HttpDownloader */
    protected $httpDownloader;
    /** @var Filesystem */
    protected $filesystem;
    /** @var Cache */
    protected $cache;
    /** @var EventDispatcher */
    protected $eventDispatcher;
    /** @var ProcessExecutor */
    protected $process;
    /**
     * @private this is only public for php 5.3 support in closures
     */
    public $lastCacheWrites = array();
    private $additionalCleanupPaths = array();
    /**
     * Constructor.
     *
     * @param IOInterface     $io              The IO instance
     * @param Config          $config          The config
     * @param HttpDownloader  $httpDownloader  The remote filesystem
     * @param EventDispatcher $eventDispatcher The event dispatcher
     * @param Cache           $cache           Cache instance
     * @param Filesystem      $filesystem      The filesystem
     */
    public function __construct(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Util\HttpDownloader $httpDownloader, \RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher $eventDispatcher = null, \RectorPrefix20210503\Composer\Cache $cache = null, \RectorPrefix20210503\Composer\Util\Filesystem $filesystem = null, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process = null)
    {
        $this->io = $io;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->httpDownloader = $httpDownloader;
        $this->cache = $cache;
        $this->process = $process ?: new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
        $this->filesystem = $filesystem ?: new \RectorPrefix20210503\Composer\Util\Filesystem($this->process);
        if ($this->cache && $this->cache->gcIsNecessary()) {
            $this->cache->gc($config->get('cache-files-ttl'), $config->get('cache-files-maxsize'));
        }
    }
    /**
     * {@inheritDoc}
     */
    public function getInstallationSource()
    {
        return 'dist';
    }
    /**
     * {@inheritDoc}
     */
    public function download(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null, $output = \true)
    {
        if (!$package->getDistUrl()) {
            throw new \InvalidArgumentException('The given package is missing url information');
        }
        $cacheKeyGenerator = function (\RectorPrefix20210503\Composer\Package\PackageInterface $package, $key) {
            $cacheKey = \sha1($key);
            return $package->getName() . '/' . $cacheKey . '.' . $package->getDistType();
        };
        $retries = 3;
        $urls = $package->getDistUrls();
        foreach ($urls as $index => $url) {
            $processedUrl = $this->processUrl($package, $url);
            $urls[$index] = array(
                'base' => $url,
                'processed' => $processedUrl,
                // we use the complete download url here to avoid conflicting entries
                // from different packages, which would potentially allow a given package
                // in a third party repo to pre-populate the cache for the same package in
                // packagist for example.
                'cacheKey' => $cacheKeyGenerator($package, $processedUrl),
            );
        }
        $fileName = $this->getFileName($package, $path);
        $this->filesystem->ensureDirectoryExists($path);
        $this->filesystem->ensureDirectoryExists(\dirname($fileName));
        $io = $this->io;
        $cache = $this->cache;
        $httpDownloader = $this->httpDownloader;
        $eventDispatcher = $this->eventDispatcher;
        $filesystem = $this->filesystem;
        $self = $this;
        $accept = null;
        $reject = null;
        $download = function () use($io, $output, $httpDownloader, $cache, $cacheKeyGenerator, $eventDispatcher, $package, $fileName, &$urls, &$accept, &$reject) {
            $url = \reset($urls);
            $index = \key($urls);
            if ($eventDispatcher) {
                $preFileDownloadEvent = new \RectorPrefix20210503\Composer\Plugin\PreFileDownloadEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::PRE_FILE_DOWNLOAD, $httpDownloader, $url['processed'], 'package', $package);
                $eventDispatcher->dispatch($preFileDownloadEvent->getName(), $preFileDownloadEvent);
                if ($preFileDownloadEvent->getCustomCacheKey() !== null) {
                    $url['cacheKey'] = $cacheKeyGenerator($package, $preFileDownloadEvent->getCustomCacheKey());
                } elseif ($preFileDownloadEvent->getProcessedUrl() !== $url['processed']) {
                    $url['cacheKey'] = $cacheKeyGenerator($package, $preFileDownloadEvent->getProcessedUrl());
                }
                $url['processed'] = $preFileDownloadEvent->getProcessedUrl();
            }
            $urls[$index] = $url;
            $checksum = $package->getDistSha1Checksum();
            $cacheKey = $url['cacheKey'];
            // use from cache if it is present and has a valid checksum or we have no checksum to check against
            if ($cache && (!$checksum || $checksum === $cache->sha1($cacheKey)) && $cache->copyTo($cacheKey, $fileName)) {
                if ($output) {
                    $io->writeError("  - Loading <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>) from cache", \true, \RectorPrefix20210503\Composer\IO\IOInterface::VERY_VERBOSE);
                }
                $result = \RectorPrefix20210503\React\Promise\resolve($fileName);
            } else {
                if ($output) {
                    $io->writeError("  - Downloading <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>)");
                }
                $result = $httpDownloader->addCopy($url['processed'], $fileName, $package->getTransportOptions())->then($accept, $reject);
            }
            return $result->then(function ($result) use($fileName, $checksum, $url, $package, $eventDispatcher) {
                // in case of retry, the first call's Promise chain finally calls this twice at the end,
                // once with $result being the returned $fileName from $accept, and then once for every
                // failed request with a null result, which can be skipped.
                if (null === $result) {
                    return $fileName;
                }
                if (!\file_exists($fileName)) {
                    throw new \UnexpectedValueException($url['base'] . ' could not be saved to ' . $fileName . ', make sure the' . ' directory is writable and you have internet connectivity');
                }
                if ($checksum && \hash_file('sha1', $fileName) !== $checksum) {
                    throw new \UnexpectedValueException('The checksum verification of the file failed (downloaded from ' . $url['base'] . ')');
                }
                if ($eventDispatcher) {
                    $postFileDownloadEvent = new \RectorPrefix20210503\Composer\Plugin\PostFileDownloadEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::POST_FILE_DOWNLOAD, $fileName, $checksum, $url['processed'], $package);
                    $eventDispatcher->dispatch($postFileDownloadEvent->getName(), $postFileDownloadEvent);
                }
                return $fileName;
            });
        };
        $accept = function ($response) use($cache, $package, $fileName, $self, &$urls) {
            $url = \reset($urls);
            $cacheKey = $url['cacheKey'];
            if ($cache && !$cache->isReadOnly()) {
                $self->lastCacheWrites[$package->getName()] = $cacheKey;
                $cache->copyFrom($cacheKey, $fileName);
            }
            $response->collect();
            return $fileName;
        };
        $reject = function ($e) use($io, &$urls, $download, $fileName, $package, &$retries, $filesystem, $self) {
            // clean up
            if (\file_exists($fileName)) {
                $filesystem->unlink($fileName);
            }
            $self->clearLastCacheWrite($package);
            if ($e instanceof \RectorPrefix20210503\Composer\Exception\IrrecoverableDownloadException) {
                throw $e;
            }
            if ($e instanceof \RectorPrefix20210503\Composer\Downloader\MaxFileSizeExceededException) {
                throw $e;
            }
            if ($e instanceof \RectorPrefix20210503\Composer\Downloader\TransportException) {
                // if we got an http response with a proper code, then requesting again will probably not help, abort
                if (0 !== $e->getCode() && !\in_array($e->getCode(), array(500, 502, 503, 504)) || !$retries) {
                    $retries = 0;
                }
            }
            // special error code returned when network is being artificially disabled
            if ($e instanceof \RectorPrefix20210503\Composer\Downloader\TransportException && $e->getStatusCode() === 499) {
                $retries = 0;
                $urls = array();
            }
            if ($retries) {
                \usleep(500000);
                $retries--;
                return $download();
            }
            \array_shift($urls);
            if ($urls) {
                if ($io->isDebug()) {
                    $io->writeError('    Failed downloading ' . $package->getName() . ': [' . \get_class($e) . '] ' . $e->getCode() . ': ' . $e->getMessage());
                    $io->writeError('    Trying the next URL for ' . $package->getName());
                } else {
                    $io->writeError('    Failed downloading ' . $package->getName() . ', trying the next URL (' . $e->getCode() . ': ' . $e->getMessage() . ')');
                }
                $retries = 3;
                \usleep(100000);
                return $download();
            }
            throw $e;
        };
        return $download();
    }
    /**
     * {@inheritDoc}
     */
    public function prepare($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup($type, \RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
        $fileName = $this->getFileName($package, $path);
        if (\file_exists($fileName)) {
            $this->filesystem->unlink($fileName);
        }
        $dirsToCleanUp = array($this->config->get('vendor-dir') . '/composer/', $this->config->get('vendor-dir'), $path);
        if (isset($this->additionalCleanupPaths[$package->getName()])) {
            foreach ($this->additionalCleanupPaths[$package->getName()] as $path) {
                $this->filesystem->remove($path);
            }
        }
        foreach ($dirsToCleanUp as $dir) {
            if (\is_dir($dir) && $this->filesystem->isDirEmpty($dir) && \realpath($dir) !== \getcwd()) {
                $this->filesystem->removeDirectory($dir);
            }
        }
    }
    /**
     * {@inheritDoc}
     */
    public function install(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, $output = \true)
    {
        if ($output) {
            $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation::format($package));
        }
        $this->filesystem->emptyDirectory($path);
        $this->filesystem->ensureDirectoryExists($path);
        $this->filesystem->rename($this->getFileName($package, $path), $path . '/' . \pathinfo(\parse_url($package->getDistUrl(), \PHP_URL_PATH), \PATHINFO_BASENAME));
        if ($package->getBinaries()) {
            // Single files can not have a mode set like files in archives
            // so we make sure if the file is a binary that it is executable
            foreach ($package->getBinaries() as $bin) {
                if (\file_exists($path . '/' . $bin) && !\is_executable($path . '/' . $bin)) {
                    \RectorPrefix20210503\Composer\Util\Silencer::call('chmod', $path . '/' . $bin, 0777 & ~\umask());
                }
            }
        }
    }
    /**
     * TODO mark private in v3
     * @protected This is public due to PHP 5.3
     */
    public function clearLastCacheWrite(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        if ($this->cache && isset($this->lastCacheWrites[$package->getName()])) {
            $this->cache->remove($this->lastCacheWrites[$package->getName()]);
            unset($this->lastCacheWrites[$package->getName()]);
        }
    }
    /**
     * TODO mark private in v3
     * @protected This is public due to PHP 5.3
     */
    public function addCleanupPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path)
    {
        $this->additionalCleanupPaths[$package->getName()][] = $path;
    }
    /**
     * TODO mark private in v3
     * @protected This is public due to PHP 5.3
     */
    public function removeCleanupPath(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path)
    {
        if (isset($this->additionalCleanupPaths[$package->getName()])) {
            $idx = \array_search($path, $this->additionalCleanupPaths[$package->getName()]);
            if (\false !== $idx) {
                unset($this->additionalCleanupPaths[$package->getName()][$idx]);
            }
        }
    }
    /**
     * {@inheritDoc}
     */
    public function update(\RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target, $path)
    {
        $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\UpdateOperation::format($initial, $target) . ": ", \false);
        $promise = $this->remove($initial, $path, \false);
        if ($promise === null || !$promise instanceof \RectorPrefix20210503\React\Promise\PromiseInterface) {
            $promise = \RectorPrefix20210503\React\Promise\resolve();
        }
        $self = $this;
        $io = $this->io;
        return $promise->then(function () use($self, $target, $path, $io) {
            $promise = $self->install($target, $path, \false);
            $io->writeError('');
            return $promise;
        });
    }
    /**
     * {@inheritDoc}
     */
    public function remove(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, $output = \true)
    {
        if ($output) {
            $this->io->writeError("  - " . \RectorPrefix20210503\Composer\DependencyResolver\Operation\UninstallOperation::format($package));
        }
        if (!$this->filesystem->removeDirectory($path)) {
            throw new \RuntimeException('Could not completely delete ' . $path . ', aborting.');
        }
    }
    /**
     * Gets file name for specific package
     *
     * @param  PackageInterface $package package instance
     * @param  string           $path    download path
     * @return string           file name
     */
    protected function getFileName(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path)
    {
        return \rtrim($this->config->get('vendor-dir') . '/composer/tmp-' . \md5($package . \spl_object_hash($package)) . '.' . \pathinfo(\parse_url($package->getDistUrl(), \PHP_URL_PATH), \PATHINFO_EXTENSION), '.');
    }
    /**
     * Process the download url
     *
     * @param  PackageInterface  $package package the url is coming from
     * @param  string            $url     download url
     * @throws \RuntimeException If any problem with the url
     * @return string            url
     */
    protected function processUrl(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $url)
    {
        if (!\extension_loaded('openssl') && 0 === \strpos($url, 'https:')) {
            throw new \RuntimeException('You must enable the openssl extension to download files via https');
        }
        if ($package->getDistReference()) {
            $url = \RectorPrefix20210503\Composer\Util\Url::updateDistReference($this->config, $url, $package->getDistReference());
        }
        return $url;
    }
    /**
     * {@inheritDoc}
     * @throws \RuntimeException
     */
    public function getLocalChanges(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $targetDir)
    {
        $prevIO = $this->io;
        $this->io = new \RectorPrefix20210503\Composer\IO\NullIO();
        $this->io->loadConfiguration($this->config);
        $e = null;
        $output = '';
        $targetDir = \RectorPrefix20210503\Composer\Util\Filesystem::trimTrailingSlash($targetDir);
        try {
            if (\is_dir($targetDir . '_compare')) {
                $this->filesystem->removeDirectory($targetDir . '_compare');
            }
            $this->download($package, $targetDir . '_compare', null, \false);
            $this->httpDownloader->wait();
            $this->install($package, $targetDir . '_compare', \false);
            $this->process->wait();
            $comparer = new \RectorPrefix20210503\Composer\Package\Comparer\Comparer();
            $comparer->setSource($targetDir . '_compare');
            $comparer->setUpdate($targetDir);
            $comparer->doCompare();
            $output = $comparer->getChanged(\true, \true);
            $this->filesystem->removeDirectory($targetDir . '_compare');
        } catch (\Exception $e) {
        }
        $this->io = $prevIO;
        if ($e) {
            throw $e;
        }
        return \trim($output);
    }
}
