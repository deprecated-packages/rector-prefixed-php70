<?php

declare (strict_types=1);
namespace RectorPrefix20210528\Symplify\SimplePhpDocParser\Bundle;

use RectorPrefix20210528\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use RectorPrefix20210528\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210528\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension;
final class SimplePhpDocParserBundle extends \RectorPrefix20210528\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    public function getContainerExtension()
    {
        return new \RectorPrefix20210528\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension();
    }
}
