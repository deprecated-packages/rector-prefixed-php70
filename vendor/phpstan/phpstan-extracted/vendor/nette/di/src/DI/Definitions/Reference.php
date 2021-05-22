<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Reference to service. Either by name or by type or reference to the 'self' service.
 */
final class Reference
{
    use Nette\SmartObject;
    const SELF = 'self';
    /** @var string */
    private $value;
    /**
     * @return $this
     */
    public static function fromType(string $value)
    {
        if (\strpos($value, '\\') === \false) {
            $value = '\\' . $value;
        }
        return new static($value);
    }
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function getValue() : string
    {
        return $this->value;
    }
    public function isName() : bool
    {
        return \strpos($this->value, '\\') === \false && $this->value !== self::SELF;
    }
    public function isType() : bool
    {
        return \strpos($this->value, '\\') !== \false;
    }
    public function isSelf() : bool
    {
        return $this->value === self::SELF;
    }
}
