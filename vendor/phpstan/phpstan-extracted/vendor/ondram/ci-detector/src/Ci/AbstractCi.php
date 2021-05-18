<?php

declare (strict_types=1);
namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env;
/**
 * Unified adapter to retrieve environment variables from current continuous integration server
 */
abstract class AbstractCi implements \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Ci\CiInterface
{
    /** @var Env */
    protected $env;
    public function __construct(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\OndraM\CiDetector\Env $env)
    {
        $this->env = $env;
    }
}
