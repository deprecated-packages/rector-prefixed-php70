<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Validation error.
 */
class ValidationException extends \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException
{
    /** @var array */
    private $messages;
    public function __construct(string $message, array $messages = [])
    {
        parent::__construct($message);
        $this->messages = $messages ?: [$message];
    }
    public function getMessages() : array
    {
        return $this->messages;
    }
}
