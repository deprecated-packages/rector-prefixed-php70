<?php

declare (strict_types=1);
namespace RectorPrefix20210527;

use Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector;
use RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../../../../../config/config.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector::class);
};
