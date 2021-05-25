<?php

namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Psr\Log\LoggerInterface $logger);
}
