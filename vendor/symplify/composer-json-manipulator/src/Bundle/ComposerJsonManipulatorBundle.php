<?php

declare (strict_types=1);
namespace RectorPrefix20210531\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210531\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210531\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210531\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210531\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}
