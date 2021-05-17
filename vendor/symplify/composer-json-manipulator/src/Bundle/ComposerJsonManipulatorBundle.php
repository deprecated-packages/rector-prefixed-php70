<?php

declare (strict_types=1);
namespace RectorPrefix20210517\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210517\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210517\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210517\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210517\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}
