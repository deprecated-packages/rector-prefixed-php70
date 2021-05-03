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
/**
 * DVCS Downloader interface.
 *
 * @author James Titcumb <james@asgrim.com>
 */
interface DvcsDownloaderInterface
{
    /**
     * Checks for unpushed changes to a current branch
     *
     * @param  PackageInterface $package package directory
     * @param  string           $path    package directory
     * @return string|null      changes or null
     */
    public function getUnpushedChanges(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $path);
}
