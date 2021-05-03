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
 * Xz archive downloader.
 *
 * @author Pavel Puchkin <i@neoascetic.me>
 * @author Pierre Rudloff <contact@rudloff.pro>
 */
class XzDownloader extends \RectorPrefix20210503\Composer\Downloader\ArchiveDownloader
{
    protected function extract(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $file, $path)
    {
        $command = 'tar -xJf ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($file) . ' -C ' . \RectorPrefix20210503\Composer\Util\ProcessExecutor::escape($path);
        if (0 === $this->process->execute($command, $ignoredOutput)) {
            return;
        }
        $processError = 'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput();
        throw new \RuntimeException($processError);
    }
}
