<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Elements;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Context;
/**
 * @internal
 */
trait Base
{
    /** @var bool */
    private $required = \false;
    /** @var mixed */
    private $default;
    /** @var callable|null */
    private $before;
    /** @var array[] */
    private $asserts = [];
    /** @var string|null */
    private $castTo;
    /**
     * @return $this
     */
    public function default($value)
    {
        $this->default = $value;
        return $this;
    }
    /**
     * @return $this
     */
    public function required(bool $state = \true)
    {
        $this->required = $state;
        return $this;
    }
    /**
     * @return $this
     */
    public function before(callable $handler)
    {
        $this->before = $handler;
        return $this;
    }
    /**
     * @return $this
     */
    public function castTo(string $type)
    {
        $this->castTo = $type;
        return $this;
    }
    /**
     * @return $this
     */
    public function assert(callable $handler, string $description = null)
    {
        $this->asserts[] = [$handler, $description];
        return $this;
    }
    public function completeDefault(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Context $context)
    {
        if ($this->required) {
            $context->addError('The mandatory option %path% is missing.');
            return null;
        }
        return $this->default;
    }
    public function doNormalize($value, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Context $context)
    {
        if ($this->before) {
            $value = ($this->before)($value);
        }
        return $value;
    }
    private function doValidate($value, string $expected, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Context $context) : bool
    {
        try {
            \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Validators::assert($value, $expected, 'option %path%');
            return \true;
        } catch (\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\AssertionException $e) {
            $context->addError($e->getMessage(), $expected);
            return \false;
        }
    }
    private function doFinalize($value, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Context $context)
    {
        if ($this->castTo) {
            if (\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::isBuiltinType($this->castTo)) {
                \settype($value, $this->castTo);
            } else {
                $value = \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Arrays::toObject($value, new $this->castTo());
            }
        }
        foreach ($this->asserts as $i => list($handler, $description)) {
            if (!$handler($value)) {
                $expected = $description ? '"' . $description . '"' : (\is_string($handler) ? "{$handler}()" : "#{$i}");
                $context->addError("Failed assertion {$expected} for option %path% with value " . static::formatValue($value) . '.');
                return;
            }
        }
        return $value;
    }
    private static function formatValue($value) : string
    {
        if (\is_string($value)) {
            return "'{$value}'";
        } elseif (\is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (\is_scalar($value)) {
            return (string) $value;
        } else {
            return \strtolower(\gettype($value));
        }
    }
}
