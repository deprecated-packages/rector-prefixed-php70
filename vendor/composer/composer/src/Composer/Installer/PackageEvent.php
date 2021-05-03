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
namespace RectorPrefix20210503\Composer\Installer;

use RectorPrefix20210503\Composer\Composer;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\OperationInterface;
use RectorPrefix20210503\Composer\Repository\RepositoryInterface;
use RectorPrefix20210503\Composer\EventDispatcher\Event;
/**
 * The Package Event.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PackageEvent extends \RectorPrefix20210503\Composer\EventDispatcher\Event
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    /**
     * @var bool
     */
    private $devMode;
    /**
     * @var RepositoryInterface
     */
    private $localRepo;
    /**
     * @var OperationInterface[]
     */
    private $operations;
    /**
     * @var OperationInterface The operation instance which is being executed
     */
    private $operation;
    /**
     * Constructor.
     *
     * @param string               $eventName
     * @param Composer             $composer
     * @param IOInterface          $io
     * @param bool                 $devMode
     * @param RepositoryInterface  $localRepo
     * @param OperationInterface[] $operations
     * @param OperationInterface   $operation
     */
    public function __construct($eventName, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\IO\IOInterface $io, $devMode, \RectorPrefix20210503\Composer\Repository\RepositoryInterface $localRepo, array $operations, \RectorPrefix20210503\Composer\DependencyResolver\Operation\OperationInterface $operation)
    {
        parent::__construct($eventName);
        $this->composer = $composer;
        $this->io = $io;
        $this->devMode = $devMode;
        $this->localRepo = $localRepo;
        $this->operations = $operations;
        $this->operation = $operation;
    }
    /**
     * @return Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }
    /**
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }
    /**
     * @return bool
     */
    public function isDevMode()
    {
        return $this->devMode;
    }
    /**
     * @return RepositoryInterface
     */
    public function getLocalRepo()
    {
        return $this->localRepo;
    }
    /**
     * @return OperationInterface[]
     */
    public function getOperations()
    {
        return $this->operations;
    }
    /**
     * Returns the package instance.
     *
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
