<?php

declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\OndraM\CiDetector;

/**
 * Encapsulate access to the environment variables
 */
class Env
{
    /**
     * @return string|false Environment variable value or false if the variable does not exist
     */
    public function get(string $name)
    {
        return \getenv($name);
    }
    public function getString(string $name) : string
    {
        return (string) $this->get($name);
    }
}
