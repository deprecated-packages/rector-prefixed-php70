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
namespace RectorPrefix20210503\Composer\Package;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class RootAliasPackage extends \RectorPrefix20210503\Composer\Package\AliasPackage implements \RectorPrefix20210503\Composer\Package\RootPackageInterface
{
    public function __construct(\RectorPrefix20210503\Composer\Package\RootPackageInterface $aliasOf, $version, $prettyVersion)
    {
        parent::__construct($aliasOf, $version, $prettyVersion);
    }
    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return $this->aliasOf->getAliases();
    }
    /**
     * {@inheritDoc}
     */
    public function getMinimumStability()
    {
        return $this->aliasOf->getMinimumStability();
    }
    /**
     * {@inheritDoc}
     */
    public function getStabilityFlags()
    {
        return $this->aliasOf->getStabilityFlags();
    }
    /**
     * {@inheritDoc}
     */
    public function getReferences()
    {
        return $this->aliasOf->getReferences();
    }
    /**
     * {@inheritDoc}
     */
    public function getPreferStable()
    {
        return $this->aliasOf->getPreferStable();
    }
    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return $this->aliasOf->getConfig();
    }
    /**
     * {@inheritDoc}
     */
    public function setRequires(array $require)
    {
        $this->requires = $this->replaceSelfVersionDependencies($require, \RectorPrefix20210503\Composer\Package\Link::TYPE_REQUIRE);
        $this->aliasOf->setRequires($require);
    }
    /**
     * {@inheritDoc}
     */
    public function setDevRequires(array $devRequire)
    {
        $this->devRequires = $this->replaceSelfVersionDependencies($devRequire, \RectorPrefix20210503\Composer\Package\Link::TYPE_DEV_REQUIRE);
        $this->aliasOf->setDevRequires($devRequire);
    }
    /**
     * {@inheritDoc}
     */
    public function setConflicts(array $conflicts)
    {
        $this->conflicts = $this->replaceSelfVersionDependencies($conflicts, \RectorPrefix20210503\Composer\Package\Link::TYPE_CONFLICT);
        $this->aliasOf->setConflicts($conflicts);
    }
    /**
     * {@inheritDoc}
     */
    public function setProvides(array $provides)
    {
        $this->provides = $this->replaceSelfVersionDependencies($provides, \RectorPrefix20210503\Composer\Package\Link::TYPE_PROVIDE);
        $this->aliasOf->setProvides($provides);
    }
    /**
     * {@inheritDoc}
     */
    public function setReplaces(array $replaces)
    {
        $this->replaces = $this->replaceSelfVersionDependencies($replaces, \RectorPrefix20210503\Composer\Package\Link::TYPE_REPLACE);
        $this->aliasOf->setReplaces($replaces);
    }
    /**
     * {@inheritDoc}
     */
    public function setRepositories($repositories)
    {
        $this->aliasOf->setRepositories($repositories);
    }
    /**
     * {@inheritDoc}
     */
    public function setAutoload(array $autoload)
    {
        $this->aliasOf->setAutoload($autoload);
    }
    /**
     * {@inheritDoc}
     */
    public function setDevAutoload(array $devAutoload)
    {
        $this->aliasOf->setDevAutoload($devAutoload);
    }
    /**
     * {@inheritDoc}
     */
    public function setStabilityFlags(array $stabilityFlags)
    {
        $this->aliasOf->setStabilityFlags($stabilityFlags);
    }
    /**
     * {@inheritDoc}
     */
    public function setSuggests(array $suggests)
    {
        $this->aliasOf->setSuggests($suggests);
    }
    /**
     * {@inheritDoc}
     */
    public function setExtra(array $extra)
    {
        $this->aliasOf->setExtra($extra);
    }
    public function __clone()
    {
        parent::__clone();
        $this->aliasOf = clone $this->aliasOf;
    }
}
