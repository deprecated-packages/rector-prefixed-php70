<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Traits;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Attribute;
/**
 * @internal
 */
trait AttributeAware
{
    /** @var Attribute[] */
    private $attributes = [];
    /** @return static */
    public function addAttribute(string $name, array $args = [])
    {
        $this->attributes[] = new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Attribute($name, $args);
        return $this;
    }
    /**
     * @param  Attribute[]  $attrs
     * @return static
     */
    public function setAttributes(array $attrs)
    {
        (function (\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Attribute ...$attrs) {
        })(...$attrs);
        $this->attributes = $attrs;
        return $this;
    }
    /** @return Attribute[] */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
}
