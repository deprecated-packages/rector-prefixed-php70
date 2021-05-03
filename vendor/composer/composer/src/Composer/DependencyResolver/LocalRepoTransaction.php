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
namespace RectorPrefix20210503\Composer\DependencyResolver;

use RectorPrefix20210503\Composer\Repository\RepositoryInterface;
/**
 * @author Nils Adermann <naderman@naderman.de>
 * @internal
 */
class LocalRepoTransaction extends \RectorPrefix20210503\Composer\DependencyResolver\Transaction
{
    public function __construct(\RectorPrefix20210503\Composer\Repository\RepositoryInterface $lockedRepository, $localRepository)
    {
        parent::__construct($localRepository->getPackages(), $lockedRepository->getPackages());
    }
}
