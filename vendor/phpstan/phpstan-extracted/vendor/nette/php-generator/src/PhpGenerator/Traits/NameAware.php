<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Traits;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * @internal
 */
trait NameAware
{
    /** @var string */
    private $name;
    public function __construct(string $name)
    {
        if (!\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::isIdentifier($name)) {
            throw new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Value '{$name}' is not valid name.");
        }
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * Returns clone with a different name.
     * @return static
     */
    public function cloneWithName(string $name)
    {
        $dolly = clone $this;
        $dolly->__construct($name);
        return $dolly;
    }
}
