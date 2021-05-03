<?php

declare (strict_types=1);
namespace RectorPrefix20210503\Symplify\ConsoleColorDiff\Bundle;

use RectorPrefix20210503\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210503\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension;
final class ConsoleColorDiffBundle extends \RectorPrefix20210503\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210503\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension();
    }
}
