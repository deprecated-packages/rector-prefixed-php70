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
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
/**
 * Solver update operation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class UpdateOperation extends \RectorPrefix20210503\Composer\DependencyResolver\Operation\SolverOperation implements \RectorPrefix20210503\Composer\DependencyResolver\Operation\OperationInterface
{
    const TYPE = 'update';
    /**
     * @var PackageInterface
     */
    protected $initialPackage;
    /**
     * @var PackageInterface
     */
    protected $targetPackage;
    /**
     * @param PackageInterface $initial initial package
     * @param PackageInterface $target  target package (updated)
     */
    public function __construct(\RectorPrefix20210503\Composer\Package\PackageInterface $initial, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        $this->initialPackage = $initial;
        $this->targetPackage = $target;
    }
    /**
     * Returns initial package.
     *
     * @return PackageInterface
     */
    public function getInitialPackage()
    {
        return $this->initialPackage;
    }
    /**
     * Returns target package.
     *
     * @return PackageInterface
     */
    public function getTargetPackage()
    {
        return $this->targetPackage;
    }
    /**
     * {@inheritDoc}
     */
    public function show($lock)
    {
        return self::format($this->initialPackage, $this->targetPackage, $lock);
    }
    public static function format(\RectorPrefix20210503\Composer\Package\PackageInterface $initialPackage, \RectorPrefix20210503\Composer\Package\PackageInterface $targetPackage, $lock = \false)
    {
        $fromVersion = $initialPackage->getFullPrettyVersion();
        $toVersion = $targetPackage->getFullPrettyVersion();
        if ($fromVersion === $toVersion && $initialPackage->getSourceReference() !== $targetPackage->getSourceReference()) {
            $fromVersion = $initialPackage->getFullPrettyVersion(\true, \RectorPrefix20210503\Composer\Package\PackageInterface::DISPLAY_SOURCE_REF);
            $toVersion = $targetPackage->getFullPrettyVersion(\true, \RectorPrefix20210503\Composer\Package\PackageInterface::DISPLAY_SOURCE_REF);
        } elseif ($fromVersion === $toVersion && $initialPackage->getDistReference() !== $targetPackage->getDistReference()) {
            $fromVersion = $initialPackage->getFullPrettyVersion(\true, \RectorPrefix20210503\Composer\Package\PackageInterface::DISPLAY_DIST_REF);
            $toVersion = $targetPackage->getFullPrettyVersion(\true, \RectorPrefix20210503\Composer\Package\PackageInterface::DISPLAY_DIST_REF);
        }
        $actionName = \RectorPrefix20210503\Composer\Package\Version\VersionParser::isUpgrade($initialPackage->getVersion(), $targetPackage->getVersion()) ? 'Upgrading' : 'Downgrading';
        return $actionName . ' <info>' . $initialPackage->getPrettyName() . '</info> (<comment>' . $fromVersion . '</comment> => <comment>' . $toVersion . '</comment>)';
    }
}
