<?php

declare (strict_types=1);
namespace RectorPrefix20210525\Symplify\Skipper\HttpKernel;

use RectorPrefix20210525\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210525\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210525\Symplify\Skipper\Bundle\SkipperBundle;
use RectorPrefix20210525\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210525\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SkipperKernel extends \RectorPrefix20210525\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @return void
     */
    public function registerContainerConfiguration(\RectorPrefix20210525\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/../../config/config.php');
        parent::registerContainerConfiguration($loader);
    }
    /**
     * @return mixed[]
     */
    public function registerBundles()
    {
        return [new \RectorPrefix20210525\Symplify\Skipper\Bundle\SkipperBundle(), new \RectorPrefix20210525\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle()];
    }
}
