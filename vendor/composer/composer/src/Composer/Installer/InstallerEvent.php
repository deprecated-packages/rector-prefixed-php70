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
use RectorPrefix20210503\Composer\DependencyResolver\Transaction;
use RectorPrefix20210503\Composer\EventDispatcher\Event;
use RectorPrefix20210503\Composer\IO\IOInterface;
class InstallerEvent extends \RectorPrefix20210503\Composer\EventDispatcher\Event
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
     * @var bool
     */
    private $executeOperations;
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * Constructor.
     *
     * @param string      $eventName
     * @param Composer    $composer
     * @param IOInterface $io
     * @param bool        $devMode
     * @param bool        $executeOperations
     * @param Transaction $transaction
     */
    public function __construct($eventName, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\IO\IOInterface $io, $devMode, $executeOperations, \RectorPrefix20210503\Composer\DependencyResolver\Transaction $transaction)
    {
        parent::__construct($eventName);
        $this->composer = $composer;
        $this->io = $io;
        $this->devMode = $devMode;
        $this->executeOperations = $executeOperations;
        $this->transaction = $transaction;
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
     * @return bool
     */
    public function isExecutingOperations()
    {
        return $this->executeOperations;
    }
    /**
     * @return Transaction|null
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
