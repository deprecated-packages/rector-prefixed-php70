<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema;

interface Schema
{
    /**
     * Normalization.
     * @return mixed
     */
    function normalize($value, \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context);
    /**
     * Merging.
     * @return mixed
     */
    function merge($value, $base);
    /**
     * Validation and finalization.
     * @return mixed
     */
    function complete($value, \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context);
    /**
     * @return mixed
     */
    function completeDefault(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context);
}
