<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema;
final class Structure implements \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
{
    use Base;
    use Nette\SmartObject;
    /** @var Schema[] */
    private $items;
    /** @var Schema|null  for array|list */
    private $otherItems;
    /** @var array */
    private $range = [null, null];
    /**
     * @param  Schema[]  $items
     */
    public function __construct(array $items)
    {
        (function (\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema ...$items) {
        })(...\array_values($items));
        $this->items = $items;
        $this->castTo = 'object';
    }
    /**
     * @return $this
     */
    public function default($value)
    {
        throw new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException('Structure cannot have default value.');
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
    public function otherItems($type = 'mixed')
    {
        $this->otherItems = $type instanceof \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema ? $type : new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type($type);
        return $this;
    }
    /********************* processing ****************d*g**/
    public function normalize($value, \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        $value = $this->doNormalize($value, $context);
        if (\is_object($value)) {
            $value = (array) $value;
        }
        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                $itemSchema = $this->items[$key] ?? $this->otherItems;
                if ($itemSchema) {
                    $context->path[] = $key;
                    $value[$key] = $itemSchema->normalize($val, $context);
                    \array_pop($context->path);
                }
            }
        }
        return $value;
    }
    public function merge($value, $base)
    {
        if (\is_array($value) && isset($value[\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::PREVENT_MERGING])) {
            unset($value[\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::PREVENT_MERGING]);
            $base = null;
        }
        if (\is_array($value) && \is_array($base)) {
            $index = 0;
            foreach ($value as $key => $val) {
                if ($key === $index) {
                    $base[] = $val;
                    $index++;
                } elseif (\array_key_exists($key, $base)) {
                    $itemSchema = $this->items[$key] ?? $this->otherItems;
                    $base[$key] = $itemSchema ? $itemSchema->merge($val, $base[$key]) : \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::merge($val, $base[$key]);
                } else {
                    $base[$key] = $val;
                }
            }
            return $base;
        }
        return \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::merge($value, $base);
    }
    public function complete($value, \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        if ($value === null) {
            $value = [];
            // is unable to distinguish null from array in NEON
        }
        $expected = 'array' . ($this->range === [null, null] ? '' : ':' . \implode('..', $this->range));
        if (!$this->doValidate($value, $expected, $context)) {
            return;
        }
        $errCount = \count($context->errors);
        $items = $this->items;
        if ($extraKeys = \array_keys(\array_diff_key($value, $items))) {
            if ($this->otherItems) {
                $items += \array_fill_keys($extraKeys, $this->otherItems);
            } else {
                $hint = \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Utils\Helpers::getSuggestion(\array_map('strval', \array_keys($items)), (string) $extraKeys[0]);
                $s = \implode("', '", \array_map(function ($key) use($context) {
                    return \implode(' › ', \array_merge($context->path, [$key]));
                }, $hint ? [$extraKeys[0]] : $extraKeys));
                $context->addError("Unexpected option '{$s}'" . ($hint ? ", did you mean '{$hint}'?" : '.'));
            }
        }
        foreach ($items as $itemKey => $itemVal) {
            $context->path[] = $itemKey;
            if (\array_key_exists($itemKey, $value)) {
                $value[$itemKey] = $itemVal->complete($value[$itemKey], $context);
            } else {
                $default = $itemVal->completeDefault($context);
                // checks required item
                if (!$context->skipDefaults) {
                    $value[$itemKey] = $default;
                }
            }
            \array_pop($context->path);
        }
        if (\count($context->errors) > $errCount) {
            return;
        }
        return $this->doFinalize($value, $context);
    }
    public function completeDefault(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        return $this->complete([], $context);
    }
}
