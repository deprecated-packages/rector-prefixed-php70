<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Neon;

/**
 * Simple parser & generator for Nette Object Notation.
 */
final class Neon
{
    const BLOCK = \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Neon\Encoder::BLOCK;
    const CHAIN = '!!chain';
    /**
     * Returns the NEON representation of a value.
     */
    public static function encode($var, int $flags = 0) : string
    {
        $encoder = new \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Neon\Encoder();
        return $encoder->encode($var, $flags);
    }
    /**
     * Decodes a NEON string.
     * @return mixed
     */
    public static function decode(string $input)
    {
        $decoder = new \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Nette\Neon\Decoder();
        return $decoder->decode($input);
    }
}
