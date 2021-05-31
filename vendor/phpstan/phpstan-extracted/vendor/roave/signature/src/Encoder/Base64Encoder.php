<?php

declare (strict_types=1);
namespace RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder;

final class Base64Encoder implements \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\EncoderInterface
{
    /**
     * {@inheritDoc}
     */
    public function encode(string $codeWithoutSignature) : string
    {
        return \base64_encode($codeWithoutSignature);
    }
    /**
     * {@inheritDoc}
     */
    public function verify(string $codeWithoutSignature, string $signature) : bool
    {
        return \hash_equals($this->encode($codeWithoutSignature), $signature);
    }
}
