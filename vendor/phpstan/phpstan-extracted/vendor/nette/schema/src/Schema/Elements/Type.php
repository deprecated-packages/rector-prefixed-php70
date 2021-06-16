<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Elements;

use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Context;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\DynamicParameter;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Helpers;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Schema;
final class Type implements \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Schema
{
    use Base;
    use Nette\SmartObject;
    /** @var string */
    private $type;
    /** @var Schema|null for arrays */
    private $items;
    /** @var array */
    private $range = [null, null];
    /** @var string|null */
    private $pattern;
    public function __construct(string $type)
    {
        static $defaults = ['list' => [], 'array' => []];
        $this->type = $type;
        $this->default = \strpos($type, '[]') ? [] : $defaults[$type] ?? null;
    }
    /**
     * @return $this
     */
    public function nullable()
    {
        $this->type .= '|null';
        return $this;
    }
    /**
     * @return $this
     */
    public function dynamic()
    {
        $this->type .= '|' . \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\DynamicParameter::class;
        return $this;
    }
    /**
     * @return $this
     * @param float|null $min
     */
    public function min($min)
    {
        $this->range[0] = $min;
        return $this;
    }
    /**
     * @return $this
     * @param float|null $max
     */
    public function max($max)
    {
        $this->range[1] = $max;
        return $this;
    }
    /**
     * @param  string|Schema  $type
     * @return $this
     */
    public function items($type = 'mixed')
    {
        $this->items = $type instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Schema ? $type : new self($type);
        return $this;
    }
    /**
     * @return $this
     * @param string|null $pattern
     */
    public function pattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }
    /********************* processing ****************d*g**/
    public function normalize($value, \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Context $context)
    {
        $value = $this->doNormalize($value, $context);
        if (\is_array($value) && $this->items) {
            foreach ($value as $key => $val) {
                $context->path[] = $key;
                $value[$key] = $this->items->normalize($val, $context);
                \array_pop($context->path);
            }
        }
        return $value;
    }
    public function merge($value, $base)
    {
        if (\is_array($value) && isset($value[\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Helpers::PREVENT_MERGING])) {
            unset($value[\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Helpers::PREVENT_MERGING]);
            return $value;
        }
        if (\is_array($value) && \is_array($base) && $this->items) {
            $index = 0;
            foreach ($value as $key => $val) {
                if ($key === $index) {
                    $base[] = $val;
                    $index++;
                } else {
                    $base[$key] = \array_key_exists($key, $base) ? $this->items->merge($val, $base[$key]) : $val;
                }
            }
            return $base;
        }
        return \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Helpers::merge($value, $base);
    }
    public function complete($value, \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Context $context)
    {
        if ($value === null && \is_array($this->default)) {
            $value = [];
            // is unable to distinguish null from array in NEON
        }
        $expected = $this->type . ($this->range === [null, null] ? '' : ':' . \implode('..', $this->range));
        if (!$this->doValidate($value, $expected, $context)) {
            return;
        }
        if ($this->pattern !== null && !\preg_match("\1^(?:{$this->pattern})\$\1Du", $value)) {
            $context->addError("The option %path% expects to match pattern '{$this->pattern}', '{$value}' given.");
            return;
        }
        if ($value instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\DynamicParameter) {
            $context->dynamics[] = [$value, \str_replace('|' . \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\DynamicParameter::class, '', $expected)];
        }
        if ($this->items) {
            $errCount = \count($context->errors);
            foreach ($value as $key => $val) {
                $context->path[] = $key;
                $value[$key] = $this->items->complete($val, $context);
                \array_pop($context->path);
            }
            if (\count($context->errors) > $errCount) {
                return null;
            }
        }
        $value = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\Schema\Helpers::merge($value, $this->default);
        return $this->doFinalize($value, $context);
    }
}
