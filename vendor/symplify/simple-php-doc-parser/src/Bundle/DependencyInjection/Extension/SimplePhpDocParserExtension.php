<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension;

use RectorPrefix20210504\Symfony\Component\Config\FileLocator;
use RectorPrefix20210504\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210504\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class SimplePhpDocParserExtension extends \RectorPrefix20210504\Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param string[] $configs
     * @return void
     */
    public function load(array $configs, \RectorPrefix20210504\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $phpFileLoader = new \RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \RectorPrefix20210504\Symfony\Component\Config\FileLocator(__DIR__ . '/../../../../config'));
        $phpFileLoader->load('config.php');
    }
}
