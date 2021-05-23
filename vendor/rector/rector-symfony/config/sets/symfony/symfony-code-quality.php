<?php

declare (strict_types=1);
namespace RectorPrefix20210523;

use Rector\Symfony\Rector\Attribute\ExtractAttributeRouteNameConstantsRector;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector::class);
    $services->set(\Rector\Symfony\Rector\Class_\MakeCommandLazyRector::class);
    $services->set(\Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector::class);
    $services->set(\Rector\Symfony\Rector\Attribute\ExtractAttributeRouteNameConstantsRector::class);
};
