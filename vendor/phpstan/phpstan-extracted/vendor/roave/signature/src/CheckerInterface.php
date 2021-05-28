<?php

declare (strict_types=1);
namespace RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Roave\Signature;

interface CheckerInterface
{
    /**
     * @param string $phpCode
     *
     * @return bool
     */
    public function check(string $phpCode) : bool;
}
