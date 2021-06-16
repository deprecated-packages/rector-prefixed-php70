<?php

declare (strict_types=1);
namespace Rector\ListReporting\Contract\Output;

use Rector\Core\Contract\Rector\RectorInterface;
interface ShowOutputFormatterInterface
{
    /**
     * @param RectorInterface[] $rectors
     * @return void
     */
    public function list(array $rectors);
    public function getName() : string;
}
