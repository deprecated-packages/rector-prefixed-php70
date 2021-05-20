<?php

declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Roave\Signature;

interface SignerInterface
{
    public function sign(string $phpCode) : string;
}
