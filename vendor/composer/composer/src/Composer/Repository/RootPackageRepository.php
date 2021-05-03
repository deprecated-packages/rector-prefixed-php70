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
namespace RectorPrefix20210503\Composer\Repository;

use RectorPrefix20210503\Composer\Package\RootPackageInterface;
/**
 * Root package repository.
 *
 * This is used for serving the RootPackage inside an in-memory InstalledRepository
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class RootPackageRepository extends \RectorPrefix20210503\Composer\Repository\ArrayRepository
{
    public function __construct(\RectorPrefix20210503\Composer\Package\RootPackageInterface $package)
    {
        parent::__construct(array($package));
    }
    public function getRepoName()
    {
        return 'root package repo';
    }
}
