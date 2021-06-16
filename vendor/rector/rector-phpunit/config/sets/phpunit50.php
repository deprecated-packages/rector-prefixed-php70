<?php

declare (strict_types=1);
namespace RectorPrefix20210616;

use Rector\PHPUnit\Rector\StaticCall\GetMockRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\PHPUnit\Rector\StaticCall\GetMockRector::class);
};
