<?php

declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci;

use RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env;
/**
 * Unified adapter to retrieve environment variables from current continuous integration server
 */
abstract class AbstractCi implements \RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Ci\CiInterface
{
    /** @var Env */
    protected $env;
    public function __construct(\RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector\Env $env)
    {
        $this->env = $env;
    }
}
