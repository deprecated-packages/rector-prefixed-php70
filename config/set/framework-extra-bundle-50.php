<?php

declare (strict_types=1);
namespace RectorPrefix20210616;

use Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
use RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector::class);
};
