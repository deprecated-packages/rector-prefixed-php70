<?php

declare (strict_types=1);
namespace PHPStan\Process\Runnable;

interface RunnableQueueLogger
{
    /**
     * @return void
     */
    public function log(string $message);
}
