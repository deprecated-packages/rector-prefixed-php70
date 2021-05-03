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
namespace RectorPrefix20210503\Composer\DependencyResolver\Operation;

use RectorPrefix20210503\Composer\Package\PackageInterface;
/**
 * Solver install operation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InstallOperation extends \RectorPrefix20210503\Composer\DependencyResolver\Operation\SolverOperation implements \RectorPrefix20210503\Composer\DependencyResolver\Operation\OperationInterface
{
    const TYPE = 'install';
    /**
     * @var PackageInterface
     */
    protected $package;
    public function __construct(\RectorPrefix20210503\Composer\Package\PackageInterface $package)
    {
        $this->package = $package;
    }
    /**
     * Returns package instance.
     *
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
    /**
     * {@inheritDoc}
     */
    public function show($lock)
    {
        return self::format($this->package, $lock);
    }
    public static function format(\RectorPrefix20210503\Composer\Package\PackageInterface $package, $lock = \false)
    {
        return ($lock ? 'Locking ' : 'Installing ') . '<info>' . $package->getPrettyName() . '</info> (<comment>' . $package->getFullPrettyVersion() . '</comment>)';
    }
}
