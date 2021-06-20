<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise;

interface CancellablePromiseInterface extends \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Promise\PromiseInterface
{
    /**
     * The `cancel()` method notifies the creator of the promise that there is no
     * further interest in the results of the operation.
     *
     * Once a promise is settled (either fulfilled or rejected), calling `cancel()` on
     * a promise has no effect.
     *
     * @return void
     */
    public function cancel();
}
