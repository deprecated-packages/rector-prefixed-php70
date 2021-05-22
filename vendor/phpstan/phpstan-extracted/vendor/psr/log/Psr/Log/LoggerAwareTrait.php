<?php

namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Psr\Log;

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
    public function setLogger(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
