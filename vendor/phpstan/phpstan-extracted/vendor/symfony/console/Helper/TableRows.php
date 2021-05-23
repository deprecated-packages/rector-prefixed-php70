<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Helper;

/**
 * @internal
 */
class TableRows implements \IteratorAggregate
{
    private $generator;
    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }
    public function getIterator() : \Traversable
    {
        $g = $this->generator;
        return $g();
    }
}
