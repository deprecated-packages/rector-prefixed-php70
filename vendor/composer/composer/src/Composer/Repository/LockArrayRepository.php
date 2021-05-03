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

/**
 * Lock array repository.
 *
 * Regular array repository, only uses a different type to identify the lock file as the source of info
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class LockArrayRepository extends \RectorPrefix20210503\Composer\Repository\ArrayRepository
{
    public function getRepoName()
    {
        return 'lock repo';
    }
}
