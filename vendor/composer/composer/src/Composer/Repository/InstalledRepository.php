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

use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Semver\Constraint\ConstraintInterface;
use RectorPrefix20210503\Composer\Semver\Constraint\Constraint;
use RectorPrefix20210503\Composer\Semver\Constraint\MatchAllConstraint;
use RectorPrefix20210503\Composer\Package\RootPackageInterface;
use RectorPrefix20210503\Composer\Package\Link;
/**
 * Installed repository is a composite of all installed repo types.
 *
 * The main use case is tagging a repo as an "installed" repository, and offering a way to get providers/replacers easily.
 *
 * Installed repos are LockArrayRepository, InstalledRepositoryInterface, RootPackageRepository and PlatformRepository
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class InstalledRepository extends \RectorPrefix20210503\Composer\Repository\CompositeRepository
{
    public function findPackagesWithReplacersAndProviders($name, $constraint = null)
    {
        $name = \strtolower($name);
        if (null !== $constraint && !$constraint instanceof \RectorPrefix20210503\Composer\Semver\Constraint\ConstraintInterface) {
            $versionParser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
            $constraint = $versionParser->parseConstraints($constraint);
        }
        $matches = array();
        foreach ($this->getRepositories() as $repo) {
            foreach ($repo->getPackages() as $candidate) {
                if ($name === $candidate->getName()) {
                    if (null === $constraint || $constraint->matches(new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint('==', $candidate->getVersion()))) {
                        $matches[] = $candidate;
                    }
                    continue;
                }
                foreach (\array_merge($candidate->getProvides(), $candidate->getReplaces()) as $link) {
                    if ($name === $link->getTarget() && ($constraint === null || $link->getConstraint() === null || $constraint->matches($link->getConstraint()))) {
                        $matches[] = $candidate;
                        continue 2;
                    }
                }
            }
        }
        return $matches;
    }
    /**
     * Returns a list of links causing the requested needle packages to be installed, as an associative array with the
     * dependent's name as key, and an array containing in order the PackageInterface and Link describing the relationship
     * as values. If recursive lookup was requested a third value is returned containing an identically formed array up
     * to the root package. That third value will be false in case a circular recursion was detected.
     *
     * @param  string|string[]          $needle        The package name(s) to inspect.
     * @param  ConstraintInterface|null $constraint    Optional constraint to filter by.
     * @param  bool                     $invert        Whether to invert matches to discover reasons for the package *NOT* to be installed.
     * @param  bool                     $recurse       Whether to recursively expand the requirement tree up to the root package.
     * @param  string[]                 $packagesFound Used internally when recurring
     * @return array                    An associative array of arrays as described above.
     */
    public function getDependents($needle, $constraint = null, $invert = \false, $recurse = \true, $packagesFound = null)
    {
        $needles = \array_map('strtolower', (array) $needle);
        $results = array();
        // initialize the array with the needles before any recursion occurs
        if (null === $packagesFound) {
            $packagesFound = $needles;
        }
        // locate root package for use below
        $rootPackage = null;
        foreach ($this->getPackages() as $package) {
            if ($package instanceof \RectorPrefix20210503\Composer\Package\RootPackageInterface) {
                $rootPackage = $package;
                break;
            }
        }
        // Loop over all currently installed packages.
        foreach ($this->getPackages() as $package) {
            $links = $package->getRequires();
            // each loop needs its own "tree" as we want to show the complete dependent set of every needle
            // without warning all the time about finding circular deps
            $packagesInTree = $packagesFound;
            // Replacements are considered valid reasons for a package to be installed during forward resolution
            if (!$invert) {
                $links += $package->getReplaces();
                // On forward search, check if any replaced package was required and add the replaced
                // packages to the list of needles. Contrary to the cross-reference link check below,
                // replaced packages are the target of links.
                foreach ($package->getReplaces() as $link) {
                    foreach ($needles as $needle) {
                        if ($link->getSource() === $needle) {
                            if ($constraint === null || $link->getConstraint()->matches($constraint) === !$invert) {
                                // already displayed this node's dependencies, cutting short
                                if (\in_array($link->getTarget(), $packagesInTree)) {
                                    $results[] = array($package, $link, \false);
                                    continue;
                                }
                                $packagesInTree[] = $link->getTarget();
                                $dependents = $recurse ? $this->getDependents($link->getTarget(), null, \false, \true, $packagesInTree) : array();
                                $results[] = array($package, $link, $dependents);
                                $needles[] = $link->getTarget();
                            }
                        }
                    }
                }
            }
            // Require-dev is only relevant for the root package
            if ($package instanceof \RectorPrefix20210503\Composer\Package\RootPackageInterface) {
                $links += $package->getDevRequires();
            }
            // Cross-reference all discovered links to the needles
            foreach ($links as $link) {
                foreach ($needles as $needle) {
                    if ($link->getTarget() === $needle) {
                        if ($constraint === null || $link->getConstraint()->matches($constraint) === !$invert) {
                            // already displayed this node's dependencies, cutting short
                            if (\in_array($link->getSource(), $packagesInTree)) {
                                $results[] = array($package, $link, \false);
                                continue;
                            }
                            $packagesInTree[] = $link->getSource();
                            $dependents = $recurse ? $this->getDependents($link->getSource(), null, \false, \true, $packagesInTree) : array();
                            $results[] = array($package, $link, $dependents);
                        }
                    }
                }
            }
            // When inverting, we need to check for conflicts of the needles against installed packages
            if ($invert && \in_array($package->getName(), $needles)) {
                foreach ($package->getConflicts() as $link) {
                    foreach ($this->findPackages($link->getTarget()) as $pkg) {
                        $version = new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint('=', $pkg->getVersion());
                        if ($link->getConstraint()->matches($version) === $invert) {
                            $results[] = array($package, $link, \false);
                        }
                    }
                }
            }
            // When inverting, we need to check for conflicts of the needles' requirements against installed packages
            if ($invert && $constraint && \in_array($package->getName(), $needles) && $constraint->matches(new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint('=', $package->getVersion()))) {
                foreach ($package->getRequires() as $link) {
                    if (\RectorPrefix20210503\Composer\Repository\PlatformRepository::isPlatformPackage($link->getTarget())) {
                        if ($this->findPackage($link->getTarget(), $link->getConstraint())) {
                            continue;
                        }
                        $platformPkg = $this->findPackage($link->getTarget(), '*');
                        $description = $platformPkg ? 'but ' . $platformPkg->getPrettyVersion() . ' is installed' : 'but it is missing';
                        $results[] = array($package, new \RectorPrefix20210503\Composer\Package\Link($package->getName(), $link->getTarget(), new \RectorPrefix20210503\Composer\Semver\Constraint\MatchAllConstraint(), \RectorPrefix20210503\Composer\Package\Link::TYPE_REQUIRE, $link->getPrettyConstraint() . ' ' . $description), \false);
                        continue;
                    }
                    foreach ($this->getPackages() as $pkg) {
                        if (!\in_array($link->getTarget(), $pkg->getNames())) {
                            continue;
                        }
                        $version = new \RectorPrefix20210503\Composer\Semver\Constraint\Constraint('=', $pkg->getVersion());
                        if ($link->getTarget() !== $pkg->getName()) {
                            foreach (\array_merge($pkg->getReplaces(), $pkg->getProvides()) as $prov) {
                                if ($link->getTarget() === $prov->getTarget()) {
                                    $version = $prov->getConstraint();
                                    break;
                                }
                            }
                        }
                        if (!$link->getConstraint()->matches($version)) {
                            // if we have a root package (we should but can not guarantee..) we show
                            // the root requires as well to perhaps allow to find an issue there
                            if ($rootPackage) {
                                foreach (\array_merge($rootPackage->getRequires(), $rootPackage->getDevRequires()) as $rootReq) {
                                    if (\in_array($rootReq->getTarget(), $pkg->getNames()) && !$rootReq->getConstraint()->matches($link->getConstraint())) {
                                        $results[] = array($package, $link, \false);
                                        $results[] = array($rootPackage, $rootReq, \false);
                                        continue 3;
                                    }
                                }
                                $results[] = array($package, $link, \false);
                                $results[] = array($rootPackage, new \RectorPrefix20210503\Composer\Package\Link($rootPackage->getName(), $link->getTarget(), new \RectorPrefix20210503\Composer\Semver\Constraint\MatchAllConstraint(), 'does not require', 'but ' . $pkg->getPrettyVersion() . ' is installed'), \false);
                            } else {
                                // no root so let's just print whatever we found
                                $results[] = array($package, $link, \false);
                            }
                        }
                        continue 2;
                    }
                }
            }
        }
        \ksort($results);
        return $results;
    }
    public function getRepoName()
    {
        return 'installed repo (' . \implode(', ', \array_map(function ($repo) {
            return $repo->getRepoName();
        }, $this->getRepositories())) . ')';
    }
    /**
     * Add a repository.
     * @param RepositoryInterface $repository
     */
    public function addRepository(\RectorPrefix20210503\Composer\Repository\RepositoryInterface $repository)
    {
        if ($repository instanceof \RectorPrefix20210503\Composer\Repository\LockArrayRepository || $repository instanceof \RectorPrefix20210503\Composer\Repository\InstalledRepositoryInterface || $repository instanceof \RectorPrefix20210503\Composer\Repository\RootPackageRepository || $repository instanceof \RectorPrefix20210503\Composer\Repository\PlatformRepository) {
            return parent::addRepository($repository);
        }
        throw new \LogicException('An InstalledRepository can not contain a repository of type ' . \get_class($repository) . ' (' . $repository->getRepoName() . ')');
    }
}
