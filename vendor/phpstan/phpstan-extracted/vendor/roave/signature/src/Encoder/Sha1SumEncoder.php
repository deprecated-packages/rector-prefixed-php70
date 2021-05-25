<?php

declare (strict_types=1);
namespace RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder;

final class Sha1SumEncoder implements \RectorPrefix20210525\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface
{
    /**
     * {@inheritDoc}
     */
    public function encode(string $codeWithoutSignature) : string
    {
        return \sha1($codeWithoutSignature);
    }
    /**
     * {@inheritDoc}
     */
    public function verify(string $codeWithoutSignature, string $signature) : bool
    {
        return \hash_equals($this->encode($codeWithoutSignature), $signature);
    }
}
