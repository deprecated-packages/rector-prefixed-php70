<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions;

use RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Constant definitions.
 */
final class ConstantsExtension extends \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\DI\CompilerExtension
{
    public function loadConfiguration()
    {
        foreach ($this->getConfig() as $name => $value) {
            $this->initialization->addBody('define(?, ?);', [$name, $value]);
        }
    }
}
