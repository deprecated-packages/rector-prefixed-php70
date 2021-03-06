<?php

declare (strict_types=1);
namespace RectorPrefix20210620\Symplify\SymplifyKernel\DependencyInjection\Extension;

use RectorPrefix20210620\Symfony\Component\Config\FileLocator;
use RectorPrefix20210620\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210620\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20210620\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class SymplifyKernelExtension extends \RectorPrefix20210620\Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param string[] $configs
     * @return void
     */
    public function load(array $configs, \RectorPrefix20210620\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $phpFileLoader = new \RectorPrefix20210620\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \RectorPrefix20210620\Symfony\Component\Config\FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('common-config.php');
    }
}
