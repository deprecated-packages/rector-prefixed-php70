<?php

declare (strict_types=1);
namespace RectorPrefix20210616;

use Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector::class);
    $services->set(\Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector::class);
};
