<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator;
/**
 * Imported service injected to the container.
 */
final class ImportedDefinition extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
{
    /** @return static
     * @param string|null $type */
    public function setType($type)
    {
        return parent::setType($type);
    }
    /**
     * @return void
     */
    public function resolveType(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
    }
    /**
     * @return void
     */
    public function complete(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
    }
    /**
     * @return void
     */
    public function generateMethod(\RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method $method, \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator $generator)
    {
        $method->setReturnType('void')->setBody('throw new Nette\\DI\\ServiceCreationException(?);', ["Unable to create imported service '{$this->getName()}', it must be added using addService()"]);
    }
    /** @deprecated use '$def instanceof ImportedDefinition' */
    public function isDynamic() : bool
    {
        return \true;
    }
}
