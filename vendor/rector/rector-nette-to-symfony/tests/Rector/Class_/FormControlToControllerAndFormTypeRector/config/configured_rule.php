<?php

declare (strict_types=1);
namespace RectorPrefix20210525;

use Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector;
use RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../../../../../config/config.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector::class);
};
