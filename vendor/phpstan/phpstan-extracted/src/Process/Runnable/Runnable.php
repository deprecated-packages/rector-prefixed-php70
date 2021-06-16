<?php

declare (strict_types=1);
namespace PHPStan\Process\Runnable;

use RectorPrefix20210616\_HumbugBox15516bb2b566\React\Promise\CancellablePromiseInterface;
interface Runnable
{
    public function getName() : string;
    public function run() : \RectorPrefix20210616\_HumbugBox15516bb2b566\React\Promise\CancellablePromiseInterface;
    /**
     * @return void
     */
    public function cancel();
}
