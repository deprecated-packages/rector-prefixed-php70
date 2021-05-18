<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema;
final class AnyOf implements \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
{
    use Base;
    use Nette\SmartObject;
    /** @var array */
    private $set;
    /**
     * @param  mixed|Schema  ...$set
     */
    public function __construct(...$set)
    {
        $this->set = $set;
    }
    /**
     * @return $this
     */
    public function nullable()
    {
        $this->set[] = null;
        return $this;
    }
    /**
     * @return $this
     */
    public function dynamic()
    {
        $this->set[] = new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\DynamicParameter::class);
        return $this;
    }
    /********************* processing ****************d*g**/
    public function normalize($value, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        return $this->doNormalize($value, $context);
    }
    public function merge($value, $base)
    {
        if (\is_array($value) && isset($value[\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::PREVENT_MERGING])) {
            unset($value[\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::PREVENT_MERGING]);
            return $value;
        }
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::merge($value, $base);
    }
    public function complete($value, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        $hints = $innerErrors = [];
        foreach ($this->set as $item) {
            if ($item instanceof \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema) {
                $dolly = new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context();
                $dolly->path = $context->path;
                $res = $item->complete($value, $dolly);
                if (!$dolly->errors) {
                    return $this->doFinalize($res, $context);
                }
                foreach ($dolly->errors as $error) {
                    if ($error->path !== $context->path || !$error->hint) {
                        $innerErrors[] = $error;
                    } else {
                        $hints[] = $error->hint;
                    }
                }
            } else {
                if ($item === $value) {
                    return $this->doFinalize($value, $context);
                }
                $hints[] = static::formatValue($item);
            }
        }
        if ($innerErrors) {
            $context->errors = \array_merge($context->errors, $innerErrors);
        } else {
            $hints = \implode('|', \array_unique($hints));
            $context->addError("The option %path% expects to be {$hints}, " . static::formatValue($value) . ' given.');
        }
    }
    public function completeDefault(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        if ($this->required) {
            $context->addError('The mandatory option %path% is missing.');
            return null;
        }
        if ($this->default instanceof \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema) {
            return $this->default->completeDefault($context);
        }
        return $this->default;
    }
}
