<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use Rector\Core\Configuration\Option;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use RectorPrefix20210518\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210518\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\RectorPrefix20210518\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, null);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Symfony\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/{Rector,ValueObject}']);
    $services->set(\Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector::class);
    $services->set(\RectorPrefix20210518\Symplify\SmartFileSystem\SmartFileSystem::class);
};
