<?php

namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\StreamInterface;
/**
 * Stream decorator that prevents a stream from being seeked
 */
class NoSeekStream extends \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\RingCentral\Psr7\StreamDecoratorTrait implements \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Psr\Http\Message\StreamInterface
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
