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

use RectorPrefix20210503\Composer\Util\IniHelper;
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RarArchive;
/**
 * RAR archive downloader.
 *
 * Based on previous work by Jordi Boggiano ({@see ZipDownloader}).
 *
 * @author Derrick Nelson <drrcknlsn@gmail.com>
 */
class RarDownloader extends \RectorPrefix20210503\Composer\Downloader\ArchiveDownloader
{
    protected function extract(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $file, $path)
    {
        $processError = null;
        // Try to use unrar on *nix
        if (!\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            $command = 'unrar x -- ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($file) . ' ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($path) . ' >/dev/null && chmod -R u+w ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($path);
            if (0 === $this->process->execute($command, $ignoredOutput)) {
                return;
            }
            $processError = 'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput();
        }
        if (!\class_exists('RarArchive')) {
            // php.ini path is added to the error message to help users find the correct file
            $iniMessage = \RectorPrefix20210503\Composer\Util\IniHelper::getMessage();
            $error = "Could not decompress the archive, enable the PHP rar extension or install unrar.\n" . $iniMessage . "\n" . $processError;
            if (!\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
                $error = "Could not decompress the archive, enable the PHP rar extension.\n" . $iniMessage;
            }
            throw new \RuntimeException($error);
        }
        $rarArchive = \RarArchive::open($file);
        if (\false === $rarArchive) {
            throw new \UnexpectedValueException('Could not open RAR archive: ' . $file);
        }
        $entries = $rarArchive->getEntries();
        if (\false === $entries) {
            throw new \RuntimeException('Could not retrieve RAR archive entries');
        }
        foreach ($entries as $entry) {
            if (\false === $entry->extract($path)) {
                throw new \RuntimeException('Could not extract entry');
            }
        }
        $rarArchive->close();
    }
}
