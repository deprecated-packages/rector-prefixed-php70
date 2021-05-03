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
use RectorPrefix20210503\Composer\Package\AliasPackage;
use RectorPrefix20210503\Composer\Package\BasePackage;
use RectorPrefix20210503\Composer\Semver\Constraint\Constraint;
/**
 * @author Nils Adermann <naderman@naderman.de>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class DefaultPolicy implements \RectorPrefix20210503\Composer\DependencyResolver\PolicyInterface
{
    private $preferStable;
    private $preferLowest;
    public function __construct($preferStable = \false, $preferLowest = \false)
    {
        $this->preferStable = $preferStable;
        $this->preferLowest = $preferLowest;
    }
    public function versionCompare(\RectorPrefix20210503\Composer\Package\PackageInterface $a, \RectorPrefix20210503\Composer\Package\PackageInterface $b, $operator)
    {
        if ($this->preferStable && ($stabA = $a->getStability()) !== ($stabB = $b->getStability())) {
            return \RectorPrefix20210503\Composer\Package\BasePackage::$stabilities[$stabA] < \RectorPrefix20210503\Composer\Package\BasePackage::$stabilities[$stabB];
        }
        $constraint = new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint($operator, $b->getVersion());
        $version = new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint('==', $a->getVersion());
        return $constraint->matchSpecific($version, \true);
    }
    public function selectPreferredPackages(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, array $literals, $requiredPackage = null)
    {
        $packages = $this->groupLiteralsByName($pool, $literals);
        $policy = $this;
        foreach ($packages as &$nameLiterals) {
            \usort($nameLiterals, function ($a, $b) use($policy, $pool, $requiredPackage) {
                return $policy->compareByPriority($pool, $pool->literalToPackage($a), $pool->literalToPackage($b), $requiredPackage, \true);
            });
        }
        foreach ($packages as &$sortedLiterals) {
            $sortedLiterals = $this->pruneToBestVersion($pool, $sortedLiterals);
            $sortedLiterals = $this->pruneRemoteAliases($pool, $sortedLiterals);
        }
        $selected = \call_user_func_array('array_merge', \array_values($packages));
        // now sort the result across all packages to respect replaces across packages
        \usort($selected, function ($a, $b) use($policy, $pool, $requiredPackage) {
            return $policy->compareByPriority($pool, $pool->literalToPackage($a), $pool->literalToPackage($b), $requiredPackage);
        });
        return $selected;
    }
    protected function groupLiteralsByName(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, $literals)
    {
        $packages = array();
        foreach ($literals as $literal) {
            $packageName = $pool->literalToPackage($literal)->getName();
            if (!isset($packages[$packageName])) {
                $packages[$packageName] = array();
            }
            $packages[$packageName][] = $literal;
        }
        return $packages;
    }
    /**
     * @protected
     */
    public function compareByPriority(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, \RectorPrefix20210503\Composer\Package\PackageInterface $a, \RectorPrefix20210503\Composer\Package\PackageInterface $b, $requiredPackage = null, $ignoreReplace = \false)
    {
        // prefer aliases to the original package
        if ($a->getName() === $b->getName()) {
            $aAliased = $a instanceof \RectorPrefix20210503\Composer\Package\AliasPackage;
            $bAliased = $b instanceof \RectorPrefix20210503\Composer\Package\AliasPackage;
            if ($aAliased && !$bAliased) {
                return -1;
                // use a
            }
            if (!$aAliased && $bAliased) {
                return 1;
                // use b
            }
        }
        if (!$ignoreReplace) {
            // return original, not replaced
            if ($this->replaces($a, $b)) {
                return 1;
                // use b
            }
            if ($this->replaces($b, $a)) {
                return -1;
                // use a
            }
            // for replacers not replacing each other, put a higher prio on replacing
            // packages with the same vendor as the required package
            if ($requiredPackage && \false !== ($pos = \strpos($requiredPackage, '/'))) {
                $requiredVendor = \substr($requiredPackage, 0, $pos);
                $aIsSameVendor = \strpos($a->getName(), $requiredVendor) === 0;
                $bIsSameVendor = \strpos($b->getName(), $requiredVendor) === 0;
                if ($bIsSameVendor !== $aIsSameVendor) {
                    return $aIsSameVendor ? -1 : 1;
                }
            }
        }
        // priority equal, sort by package id to make reproducible
        if ($a->id === $b->id) {
            return 0;
        }
        return $a->id < $b->id ? -1 : 1;
    }
    /**
     * Checks if source replaces a package with the same name as target.
     *
     * Replace constraints are ignored. This method should only be used for
     * prioritisation, not for actual constraint verification.
     *
     * @param  PackageInterface $source
     * @param  PackageInterface $target
     * @return bool
     */
    protected function replaces(\RectorPrefix20210503\Composer\Package\PackageInterface $source, \RectorPrefix20210503\Composer\Package\PackageInterface $target)
    {
        foreach ($source->getReplaces() as $link) {
            if ($link->getTarget() === $target->getName()) {
                return \true;
            }
        }
        return \false;
    }
    protected function pruneToBestVersion(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, $literals)
    {
        $operator = $this->preferLowest ? '<' : '>';
        $bestLiterals = array($literals[0]);
        $bestPackage = $pool->literalToPackage($literals[0]);
        foreach ($literals as $i => $literal) {
            if (0 === $i) {
                continue;
            }
            $package = $pool->literalToPackage($literal);
            if ($this->versionCompare($package, $bestPackage, $operator)) {
                $bestPackage = $package;
                $bestLiterals = array($literal);
            } elseif ($this->versionCompare($package, $bestPackage, '==')) {
                $bestLiterals[] = $literal;
            }
        }
        return $bestLiterals;
    }
    /**
     * Assumes that locally aliased (in root package requires) packages take priority over branch-alias ones
     *
     * If no package is a local alias, nothing happens
     */
    protected function pruneRemoteAliases(\RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, array $literals)
    {
        $hasLocalAlias = \false;
        foreach ($literals as $literal) {
            $package = $pool->literalToPackage($literal);
            if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage && $package->isRootPackageAlias()) {
                $hasLocalAlias = \true;
                break;
            }
        }
        if (!$hasLocalAlias) {
            return $literals;
        }
        $selected = array();
        foreach ($literals as $literal) {
            $package = $pool->literalToPackage($literal);
            if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage && $package->isRootPackageAlias()) {
                $selected[] = $literal;
            }
        }
        return $selected;
    }
}
