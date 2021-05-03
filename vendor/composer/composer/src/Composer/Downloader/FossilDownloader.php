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

use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
/**
 * @author BohwaZ <http://bohwaz.net/>
 */
class FossilDownloader extends \RectorPrefix20210503\Composer\Downloader\VcsDownloader
{
    /**
     * {@inheritDoc}
     */
    protected function doDownload(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, $url, \RectorPrefix20210503\Composer\Package\PackageInterface $prevPackage = null)
    {
    }
    /**
     * {@inheritDoc}
     */
    protected function doInstall(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path, $url)
    {
        // Ensure we are allowed to use this URL by config
        $this->config->prohibitUrlByConfig($url, $this->io);
        $url = \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($url);
        $ref = \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($package->getSourceReference());
        $repoFile = $path . '.fossil';
        $this->io->writeError("Cloning " . $package->getSourceReference());
        $command = \sprintf('fossil clone -- %s %s', $url, \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($repoFile));
        if (0 !== $this->process->execute($command, $ignoredOutput)) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        $command = \sprintf('fossil open --nested -- %s', \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($repoFile));
        if (0 !== $this->process->execute($command, $ignoredOutput, \realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        $command = \sprintf('fossil update -- %s', $ref);
        if (0 !== $this->process->execute($command, $ignoredOutput, \realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
    }
    /**
     * {@inheritDoc}
     */
    protected function doUpdate(\RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target, $path, $url)
    {
        // Ensure we are allowed to use this URL by config
        $this->config->prohibitUrlByConfig($url, $this->io);
        $ref = \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($target->getSourceReference());
        $this->io->writeError(" Updating to " . $target->getSourceReference());
        if (!$this->hasMetadataRepository($path)) {
            throw new \RuntimeException('The .fslckout file is missing from ' . $path . ', see https://getcomposer.org/commit-deps for more information');
        }
        $command = \sprintf('fossil pull && fossil up %s', $ref);
        if (0 !== $this->process->execute($command, $ignoredOutput, \realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
    }
    /**
     * {@inheritDoc}
     */
    public function getLocalChanges(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path)
    {
        if (!$this->hasMetadataRepository($path)) {
            return null;
        }
        $this->process->execute('fossil changes', $output, \realpath($path));
        return \trim($output) ?: null;
    }
    /**
     * {@inheritDoc}
     */
    protected function getCommitLogs($fromReference, $toReference, $path)
    {
        $command = \sprintf('fossil timeline -t ci -W 0 -n 0 before %s', \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($toReference));
        if (0 !== $this->process->execute($command, $output, \realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        $log = '';
        $match = '/\\d\\d:\\d\\d:\\d\\d\\s+\\[' . $toReference . '\\]/';
        foreach ($this->process->splitLines($output) as $line) {
            if (\preg_match($match, $line)) {
                break;
            }
            $log .= $line;
        }
        return $log;
    }
    /**
     * {@inheritDoc}
     */
    protected function hasMetadataRepository($path)
    {
        return \is_file($path . '/.fslckout') || \is_file($path . '/_FOSSIL_');
    }
}
