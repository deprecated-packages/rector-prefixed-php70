<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\Nette\Utils;

use RectorPrefix20210620\Nette;
/**
 * Provides the base class for a generic list (items can be accessed by index).
 */
class ArrayList implements \ArrayAccess, \Countable, \IteratorAggregate
{
    use Nette\SmartObject;
    /** @var mixed[] */
    private $list = [];
    /**
     * Returns an iterator over all items.
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->list);
    }
    /**
     * Returns items count.
     */
    public function count() : int
    {
        return \count($this->list);
    }
    /**
     * Replaces or appends a item.
     * @param  int|null  $index
     * @param  mixed  $value
     * @throws Nette\OutOfRangeException
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if ($index === null) {
            $this->list[] = $value;
        } elseif (!\is_int($index) || $index < 0 || $index >= \count($this->list)) {
            throw new \RectorPrefix20210620\Nette\OutOfRangeException('Offset invalid or out of range');
        } else {
            $this->list[$index] = $value;
        }
    }
    /**
     * Returns a item.
     * @param  int  $index
     * @return mixed
     * @throws Nette\OutOfRangeException
     */
    public function offsetGet($index)
    {
        if (!\is_int($index) || $index < 0 || $index >= \count($this->list)) {
            throw new \RectorPrefix20210620\Nette\OutOfRangeException('Offset invalid or out of range');
        }
        return $this->list[$index];
    }
    /**
     * Determines whether a item exists.
     * @param  int  $index
     */
    public function offsetExists($index) : bool
    {
        return \is_int($index) && $index >= 0 && $index < \count($this->list);
    }
    /**
     * Removes the element at the specified position in this list.
     * @param  int  $index
     * @throws Nette\OutOfRangeException
     * @return void
     */
    public function offsetUnset($index)
    {
        if (!\is_int($index) || $index < 0 || $index >= \count($this->list)) {
            throw new \RectorPrefix20210620\Nette\OutOfRangeException('Offset invalid or out of range');
        }
        \array_splice($this->list, $index, 1);
    }
    /**
     * Prepends a item.
     * @param  mixed  $value
     * @return void
     */
    public function prepend($value)
    {
        $first = \array_slice($this->list, 0, 1);
        $this->offsetSet(0, $value);
        \array_splice($this->list, 1, 0, $first);
    }
}
