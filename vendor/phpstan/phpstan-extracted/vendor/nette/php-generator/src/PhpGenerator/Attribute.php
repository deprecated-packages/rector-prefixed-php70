<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * PHP Attribute.
 */
final class Attribute
{
    use Nette\SmartObject;
    /** @var string */
    private $name;
    /** @var array */
    private $args;
    public function __construct(string $name, array $args)
    {
        if (!\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::isNamespaceIdentifier($name)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Value '{$name}' is not valid attribute name.");
        }
        $this->name = $name;
        $this->args = $args;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getArguments() : array
    {
        return $this->args;
    }
}
