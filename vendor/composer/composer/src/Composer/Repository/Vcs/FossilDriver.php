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
use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\IO\IOInterface;
/**
 * @author BohwaZ <http://bohwaz.net/>
 */
class FossilDriver extends \RectorPrefix20210503\Composer\Repository\Vcs\VcsDriver
{
    protected $tags;
    protected $branches;
    protected $rootIdentifier;
    protected $repoFile;
    protected $checkoutDir;
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        // Make sure fossil is installed and reachable.
        $this->checkFossil();
        // Ensure we are allowed to use this URL by config.
        $this->config->prohibitUrlByConfig($this->url, $this->io);
        // Only if url points to a locally accessible directory, assume it's the checkout directory.
        // Otherwise, it should be something fossil can clone from.
        if (\RectorPrefix20210503\Composer\Util\Filesystem::isLocalPath($this->url) && \is_dir($this->url)) {
            $this->checkoutDir = $this->url;
        } else {
            if (!\RectorPrefix20210503\Composer\Cache::isUsable($this->config->get('cache-repo-dir')) || !\RectorPrefix20210503\Composer\Cache::isUsable($this->config->get('cache-vcs-dir'))) {
                throw new \RuntimeException('FossilDriver requires a usable cache directory, and it looks like you set it to be disabled');
            }
            $localName = \preg_replace('{[^a-z0-9]}i', '-', $this->url);
            $this->repoFile = $this->config->get('cache-repo-dir') . '/' . $localName . '.fossil';
            $this->checkoutDir = $this->config->get('cache-vcs-dir') . '/' . $localName . '/';
            $this->updateLocalRepo();
        }
        $this->getTags();
        $this->getBranches();
    }
    /**
     * Check that fossil can be invoked via command line.
     */
    protected function checkFossil()
    {
        if (0 !== $this->process->execute('fossil version', $ignoredOutput)) {
            throw new \RuntimeException("fossil was not found, check that it is installed and in your PATH env.\n\n" . $this->process->getErrorOutput());
        }
    }
    /**
     * Clone or update existing local fossil repository.
     */
    protected function updateLocalRepo()
    {
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem();
        $fs->ensureDirectoryExists($this->checkoutDir);
        if (!\is_writable(\dirname($this->checkoutDir))) {
            throw new \RuntimeException('Can not clone ' . $this->url . ' to access package information. The "' . $this->checkoutDir . '" directory is not writable by the current user.');
        }
        // update the repo if it is a valid fossil repository
        if (\is_file($this->repoFile) && \is_dir($this->checkoutDir) && 0 === $this->process->execute('fossil info', $output, $this->checkoutDir)) {
            if (0 !== $this->process->execute('fossil pull', $output, $this->checkoutDir)) {
                $this->io->writeError('<error>Failed to update ' . $this->url . ', package information from this repository may be outdated (' . $this->process->getErrorOutput() . ')</error>');
            }
        } else {
            // clean up directory and do a fresh clone into it
            $fs->removeDirectory($this->checkoutDir);
            $fs->remove($this->repoFile);
            $fs->ensureDirectoryExists($this->checkoutDir);
            if (0 !== $this->process->execute(\sprintf('fossil clone -- %s %s', \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($this->url), \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($this->repoFile)), $output)) {
                $output = $this->process->getErrorOutput();
                throw new \RuntimeException('Failed to clone ' . $this->url . ' to repository ' . $this->repoFile . "\n\n" . $output);
            }
            if (0 !== $this->process->execute(\sprintf('fossil open --nested -- %s', \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($this->repoFile)), $output, $this->checkoutDir)) {
                $output = $this->process->getErrorOutput();
                throw new \RuntimeException('Failed to open repository ' . $this->repoFile . ' in ' . $this->checkoutDir . "\n\n" . $output);
            }
        }
    }
    /**
     * {@inheritDoc}
     */
    public function getRootIdentifier()
    {
        if (null === $this->rootIdentifier) {
            $this->rootIdentifier = 'trunk';
        }
        return $this->rootIdentifier;
    }
    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return $this->url;
    }
    /**
     * {@inheritDoc}
     */
    public function getSource($identifier)
    {
        return array('type' => 'fossil', 'url' => $this->getUrl(), 'reference' => $identifier);
    }
    /**
     * {@inheritDoc}
     */
    public function getDist($identifier)
    {
        return null;
    }
    /**
     * {@inheritdoc}
     */
    public function getFileContent($file, $identifier)
    {
        $command = \sprintf('fossil cat -r %s -- %s', \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($identifier), \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($file));
        $this->process->execute($command, $content, $this->checkoutDir);
        if (!\trim($content)) {
            return null;
        }
        return $content;
    }
    /**
     * {@inheritdoc}
     */
    public function getChangeDate($identifier)
    {
        $this->process->execute('fossil finfo -b -n 1 composer.json', $output, $this->checkoutDir);
        list(, $date) = \explode(' ', \trim($output), 3);
        return new \DateTime($date, new \DateTimeZone('UTC'));
    }
    /**
     * {@inheritDoc}
     */
    public function getTags()
    {
        if (null === $this->tags) {
            $tags = array();
            $this->process->execute('fossil tag list', $output, $this->checkoutDir);
            foreach ($this->process->splitLines($output) as $tag) {
                $tags[$tag] = $tag;
            }
            $this->tags = $tags;
        }
        return $this->tags;
    }
    /**
     * {@inheritDoc}
     */
    public function getBranches()
    {
        if (null === $this->branches) {
            $branches = array();
            $this->process->execute('fossil branch list', $output, $this->checkoutDir);
            foreach ($this->process->splitLines($output) as $branch) {
                $branch = \trim(\preg_replace('/^\\*/', '', \trim($branch)));
                $branches[$branch] = $branch;
            }
            $this->branches = $branches;
        }
        return $this->branches;
    }
    /**
     * {@inheritDoc}
     */
    public static function supports(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $url, $deep = \false)
    {
        if (\preg_match('#(^(?:https?|ssh)://(?:[^@]@)?(?:chiselapp\\.com|fossil\\.))#i', $url)) {
            return \true;
        }
        if (\preg_match('!/fossil/|\\.fossil!', $url)) {
            return \true;
        }
        // local filesystem
        if (\RectorPrefix20210503\Composer\Util\Filesystem::isLocalPath($url)) {
            $url = \RectorPrefix20210503\Composer\Util\Filesystem::getPlatformPath($url);
            if (!\is_dir($url)) {
                return \false;
            }
            $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
            // check whether there is a fossil repo in that path
            if ($process->execute('fossil info', $output, $url) === 0) {
                return \true;
            }
        }
        return \false;
    }
}
