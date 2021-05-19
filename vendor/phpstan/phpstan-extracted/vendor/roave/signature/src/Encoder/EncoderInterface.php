<?php

declare (strict_types=1);
namespace RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder;

interface EncoderInterface
{
    public function encode(string $codeWithoutSignature) : string;
    public function verify(string $codeWithoutSignature, string $signature) : bool;
}
