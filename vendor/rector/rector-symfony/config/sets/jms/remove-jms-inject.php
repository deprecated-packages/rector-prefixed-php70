<?php

declare (strict_types=1);
namespace RectorPrefix20210504;

use Rector\Symfony\Rector\Property\JMSInjectPropertyToConstructorInjectionRector;
use RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\Property\JMSInjectPropertyToConstructorInjectionRector::class);
};
