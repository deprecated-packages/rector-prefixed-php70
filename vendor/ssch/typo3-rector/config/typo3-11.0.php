<?php

declare (strict_types=1);
namespace RectorPrefix20210531;

use RectorPrefix20210531\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210531\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/v11/*');
};
