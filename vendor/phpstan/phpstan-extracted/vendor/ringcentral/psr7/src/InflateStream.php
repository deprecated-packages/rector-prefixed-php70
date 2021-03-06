<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface;
/**
 * Uses PHP's zlib.inflate filter to inflate deflate or gzipped content.
 *
 * This stream decorator skips the first 10 bytes of the given stream to remove
 * the gzip header, converts the provided stream to a PHP stream resource,
 * then appends the zlib.inflate filter. The stream is then converted back
 * to a Guzzle stream resource to be used as a Guzzle stream.
 *
 * @link http://tools.ietf.org/html/rfc1952
 * @link http://php.net/manual/en/filters.compression.php
 */
class InflateStream extends \RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7\StreamDecoratorTrait implements \RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface
{
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\StreamInterface $stream)
    {
        // Skip the first 10 bytes
        $stream = new \RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7\LimitStream($stream, -1, 10);
        $resource = \RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7\StreamWrapper::getResource($stream);
        \stream_filter_append($resource, 'zlib.inflate', \STREAM_FILTER_READ);
        parent::__construct(new \RectorPrefix20210620\_HumbugBox15516bb2b566\RingCentral\Psr7\Stream($resource));
    }
}
