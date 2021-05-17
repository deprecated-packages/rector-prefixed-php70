<?php

namespace RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\React\EventLoop;

interface TimerInterface
{
    /**
     * Get the interval after which this timer will execute, in seconds
     *
     * @return float
     */
    public function getInterval();
    /**
     * Get the callback that will be executed when this timer elapses
     *
     * @return callable
     */
    public function getCallback();
    /**
     * Determine whether the time is periodic
     *
     * @return bool
     */
    public function isPeriodic();
}