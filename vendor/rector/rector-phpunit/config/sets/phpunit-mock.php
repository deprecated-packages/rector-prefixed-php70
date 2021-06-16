<?php

declare (strict_types=1);
namespace RectorPrefix20210616;

use Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector;
use RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector::class);
};
