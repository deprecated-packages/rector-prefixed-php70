<?php

declare (strict_types=1);
namespace RectorPrefix20210522;

use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Laravel\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/{Rector,ValueObject}']);
    $services->set(\Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector::class);
};
