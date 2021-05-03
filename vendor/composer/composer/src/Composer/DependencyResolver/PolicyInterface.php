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

use RectorPrefix20210503\Composer\Package\PackageInterface;
/**
 * @author Nils Adermann <naderman@naderman.de>
 */
interface PolicyInterface
{
    public function versionCompare(\RectorPrefix20210503\Composer\Package\PackageInterface $a, \RectorPrefix20210503\Composer\Package\PackageInterface $b, $operator);
    public function selectPreferredPackages(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, array $literals, $requiredPackage = null);
}
