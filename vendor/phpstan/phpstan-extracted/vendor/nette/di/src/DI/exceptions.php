<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Service not found exception.
 */
class MissingServiceException extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException
{
}
/**
 * Service creation exception.
 */
class ServiceCreationException extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException
{
    /**
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }
}
/**
 * Not allowed when container is resolving.
 */
class NotAllowedDuringResolvingException extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException
{
}
/**
 * Error in configuration.
 */
class InvalidConfigurationException extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException
{
}
