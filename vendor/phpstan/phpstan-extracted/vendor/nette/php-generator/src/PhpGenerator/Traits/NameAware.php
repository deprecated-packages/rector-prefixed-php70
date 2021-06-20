<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Traits;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
/**
 * @internal
 */
trait NameAware
{
    /** @var string */
    private $name;
    public function __construct(string $name)
    {
        if (!\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Helpers::isIdentifier($name)) {
            throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\InvalidArgumentException("Value '{$name}' is not valid name.");
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
