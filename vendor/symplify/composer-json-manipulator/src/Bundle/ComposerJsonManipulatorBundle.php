<?php

declare (strict_types=1);
namespace RectorPrefix20210523\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210523\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210523\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210523\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210523\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}
