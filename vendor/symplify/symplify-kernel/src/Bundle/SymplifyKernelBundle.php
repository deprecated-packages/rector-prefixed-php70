<?php

declare (strict_types=1);
namespace RectorPrefix20210620\Symplify\SymplifyKernel\Bundle;

use RectorPrefix20210620\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210620\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210620\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20210620\Symplify\SymplifyKernel\DependencyInjection\Extension\SymplifyKernelExtension;
final class SymplifyKernelBundle extends \RectorPrefix20210620\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return void
     */
    public function build(\RectorPrefix20210620\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20210620\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass());
    }
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    protected function createContainerExtension()
    {
        return new \RectorPrefix20210620\Symplify\SymplifyKernel\DependencyInjection\Extension\SymplifyKernelExtension();
    }
}
