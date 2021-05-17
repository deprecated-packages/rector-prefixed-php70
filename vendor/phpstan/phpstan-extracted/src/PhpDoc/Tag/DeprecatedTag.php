<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

class DeprecatedTag
{
    /** @var string|null */
    private $message;
    /**
     * @param string|null $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }
}
