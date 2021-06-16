<?php

declare (strict_types=1);
namespace RectorPrefix20210616\Symplify\Astral\HttpKernel;

use RectorPrefix20210616\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210616\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class AstralKernel extends \RectorPrefix20210616\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @return void
     */
    public function registerContainerConfiguration(\RectorPrefix20210616\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/../../config/config.php');
    }
}
