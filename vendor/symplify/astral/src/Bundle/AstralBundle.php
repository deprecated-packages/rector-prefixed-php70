<?php

declare (strict_types=1);
namespace RectorPrefix20210523\Symplify\Astral\Bundle;

use RectorPrefix20210523\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210523\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210523\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
use RectorPrefix20210523\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
final class AstralBundle extends \RectorPrefix20210523\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return void
     */
    public function build(\RectorPrefix20210523\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20210523\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass());
    }
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210523\Symplify\Astral\DependencyInjection\Extension\AstralExtension();
    }
}
