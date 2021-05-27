<?php

declare (strict_types=1);
namespace RectorPrefix20210527;

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\Property\TypedPropertyRector::class);
    $services->set(\Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class);
};
