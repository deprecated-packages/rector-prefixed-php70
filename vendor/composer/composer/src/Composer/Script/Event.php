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
namespace RectorPrefix20210503\Composer\Script;

use RectorPrefix20210503\Composer\Composer;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\EventDispatcher\Event as BaseEvent;
/**
 * The script event class
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Nils Adermann <naderman@naderman.de>
 */
class Event extends \RectorPrefix20210503\Composer\EventDispatcher\Event
{
    /**
     * @var Composer The composer instance
     */
    private $composer;
    /**
     * @var IOInterface The IO instance
     */
    private $io;
    /**
     * @var bool Dev mode flag
     */
    private $devMode;
    /**
     * @var BaseEvent
     */
    private $originatingEvent;
    /**
     * Constructor.
     *
     * @param string      $name     The event name
     * @param Composer    $composer The composer object
     * @param IOInterface $io       The IOInterface object
     * @param bool        $devMode  Whether or not we are in dev mode
     * @param array       $args     Arguments passed by the user
     * @param array       $flags    Optional flags to pass data not as argument
     */
    public function __construct($name, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\IO\IOInterface $io, $devMode = \false, array $args = array(), array $flags = array())
    {
        parent::__construct($name, $args, $flags);
        $this->composer = $composer;
        $this->io = $io;
        $this->devMode = $devMode;
    }
    /**
     * Returns the composer instance.
     *
     * @return Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }
    /**
     * Returns the IO instance.
     *
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }
    /**
     * Return the dev mode flag
     *
     * @return bool
     */
    public function isDevMode()
    {
        return $this->devMode;
    }
    /**
     * Set the originating event.
     *
     * @return \Composer\EventDispatcher\Event|null
     */
    public function getOriginatingEvent()
    {
        return $this->originatingEvent;
    }
    /**
     * Set the originating event.
     *
     * @param  \Composer\EventDispatcher\Event $event
     * @return $this
     */
    public function setOriginatingEvent(\RectorPrefix20210503\Composer\EventDispatcher\Event $event)
    {
        $this->originatingEvent = $this->calculateOriginatingEvent($event);
        return $this;
    }
    /**
     * Returns the upper-most event in chain.
     *
     * @param  \Composer\EventDispatcher\Event $event
     * @return \Composer\EventDispatcher\Event
     */
    private function calculateOriginatingEvent(\RectorPrefix20210503\Composer\EventDispatcher\Event $event)
    {
        if ($event instanceof \RectorPrefix20210503\Composer\Script\Event && $event->getOriginatingEvent()) {
            return $this->calculateOriginatingEvent($event->getOriginatingEvent());
        }
        return $event;
    }
}
