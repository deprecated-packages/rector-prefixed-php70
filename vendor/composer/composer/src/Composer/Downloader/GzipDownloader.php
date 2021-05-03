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
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
/**
 * GZip archive downloader.
 *
 * @author Pavel Puchkin <i@neoascetic.me>
 */
class GzipDownloader extends \RectorPrefix20210503\Composer\Downloader\ArchiveDownloader
{
    protected function extract(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $file, $path)
    {
        $filename = \pathinfo(\parse_url($package->getDistUrl(), \PHP_URL_PATH), \PATHINFO_FILENAME);
        $targetFilepath = $path . \DIRECTORY_SEPARATOR . $filename;
        // Try to use gunzip on *nix
        if (!\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            $command = 'gzip -cd -- ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($file) . ' > ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($targetFilepath);
            if (0 === $this->process->execute($command, $ignoredOutput)) {
                return;
            }
            if (\extension_loaded('zlib')) {
                // Fallback to using the PHP extension.
                $this->extractUsingExt($file, $targetFilepath);
                return;
            }
            $processError = 'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput();
            throw new \RuntimeException($processError);
        }
        // Windows version of PHP has built-in support of gzip functions
        $this->extractUsingExt($file, $targetFilepath);
    }
    private function extractUsingExt($file, $targetFilepath)
    {
        $archiveFile = \gzopen($file, 'rb');
        $targetFile = \fopen($targetFilepath, 'wb');
        while ($string = \gzread($archiveFile, 4096)) {
            \fwrite($targetFile, $string, \RectorPrefix20210503\Composer\Util\Platform::strlen($string));
        }
        \gzclose($archiveFile);
        \fclose($targetFile);
    }
}
