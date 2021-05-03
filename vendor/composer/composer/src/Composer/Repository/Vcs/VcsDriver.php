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
namespace RectorPrefix20210503\Composer\Repository\Vcs;

use RectorPrefix20210503\Composer\Cache;
use RectorPrefix20210503\Composer\Downloader\TransportException;
use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Util\HttpDownloader;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Util\Http\Response;
/**
 * A driver implementation for driver with authentication interaction.
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 */
abstract class VcsDriver implements \RectorPrefix20210503\Composer\Repository\Vcs\VcsDriverInterface
{
    /** @var string */
    protected $url;
    /** @var string */
    protected $originUrl;
    /** @var array */
    protected $repoConfig;
    /** @var IOInterface */
    protected $io;
    /** @var Config */
    protected $config;
    /** @var ProcessExecutor */
    protected $process;
    /** @var HttpDownloader */
    protected $httpDownloader;
    /** @var array */
    protected $infoCache = array();
    /** @var Cache */
    protected $cache;
    /**
     * Constructor.
     *
     * @param array           $repoConfig     The repository configuration
     * @param IOInterface     $io             The IO instance
     * @param Config          $config         The composer configuration
     * @param HttpDownloader  $httpDownloader Remote Filesystem, injectable for mocking
     * @param ProcessExecutor $process        Process instance, injectable for mocking
     */
    public final function __construct(array $repoConfig, \RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Util\HttpDownloader $httpDownloader, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process)
    {
        if (\RectorPrefix20210503\Composer\Util\Filesystem::isLocalPath($repoConfig['url'])) {
            $repoConfig['url'] = \RectorPrefix20210503\Composer\Util\Filesystem::getPlatformPath($repoConfig['url']);
        }
        $this->url = $repoConfig['url'];
        $this->originUrl = $repoConfig['url'];
        $this->repoConfig = $repoConfig;
        $this->io = $io;
        $this->config = $config;
        $this->httpDownloader = $httpDownloader;
        $this->process = $process;
    }
    /**
     * Returns whether or not the given $identifier should be cached or not.
     *
     * @param  string $identifier
     * @return bool
     */
    protected function shouldCache($identifier)
    {
        return $this->cache && \preg_match('{^[a-f0-9]{40}$}iD', $identifier);
    }
    /**
     * {@inheritdoc}
     */
    public function getComposerInformation($identifier)
    {
        if (!isset($this->infoCache[$identifier])) {
            if ($this->shouldCache($identifier) && ($res = $this->cache->read($identifier))) {
                return $this->infoCache[$identifier] = \RectorPrefix20210503\Composer\Json\JsonFile::parseJson($res);
            }
            $composer = $this->getBaseComposerInformation($identifier);
            if ($this->shouldCache($identifier)) {
                $this->cache->write($identifier, \json_encode($composer));
            }
            $this->infoCache[$identifier] = $composer;
        }
        return $this->infoCache[$identifier];
    }
    protected function getBaseComposerInformation($identifier)
    {
        $composerFileContent = $this->getFileContent('composer.json', $identifier);
        if (!$composerFileContent) {
            return null;
        }
        $composer = \RectorPrefix20210503\Composer\Json\JsonFile::parseJson($composerFileContent, $identifier . ':composer.json');
        if (empty($composer['time']) && ($changeDate = $this->getChangeDate($identifier))) {
            $composer['time'] = $changeDate->format(\DATE_RFC3339);
        }
        return $composer;
    }
    /**
     * {@inheritDoc}
     */
    public function hasComposerFile($identifier)
    {
        try {
            return (bool) $this->getComposerInformation($identifier);
        } catch (\RectorPrefix20210503\Composer\Downloader\TransportException $e) {
        }
        return \false;
    }
    /**
     * Get the https or http protocol depending on SSL support.
     *
     * Call this only if you know that the server supports both.
     *
     * @return string The correct type of protocol
     */
    protected function getScheme()
    {
        if (\extension_loaded('openssl')) {
            return 'https';
        }
        return 'http';
    }
    /**
     * Get the remote content.
     *
     * @param string $url The URL of content
     *
     * @return Response
     */
    protected function getContents($url)
    {
        $options = isset($this->repoConfig['options']) ? $this->repoConfig['options'] : array();
        return $this->httpDownloader->get($url, $options);
    }
    /**
     * {@inheritDoc}
     */
    public function cleanup()
    {
        return;
    }
}
