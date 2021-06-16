<?php

namespace RectorPrefix20210616\_HumbugBox15516bb2b566\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(\RectorPrefix20210616\_HumbugBox15516bb2b566\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
