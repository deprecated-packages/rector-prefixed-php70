<?php

declare (strict_types=1);
namespace PHPStan\Process\Runnable;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface;
interface Runnable
{
    public function getName() : string;
    public function run() : \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\React\Promise\CancellablePromiseInterface;
    /**
     * @return void
     */
    public function cancel();
}
