<?php

namespace RectorPrefix20210616\_HumbugBox15516bb2b566\Psr\Log;

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
    public function setLogger(\RectorPrefix20210616\_HumbugBox15516bb2b566\Psr\Log\LoggerInterface $logger);
}
