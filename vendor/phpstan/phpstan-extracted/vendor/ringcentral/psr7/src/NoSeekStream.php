<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface;
/**
 * Stream decorator that prevents a stream from being seeked
 */
class NoSeekStream extends \RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7\StreamDecoratorTrait implements \RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface
{
    public function seek($offset, $whence = \SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }
    public function isSeekable()
    {
        return \false;
    }
}
