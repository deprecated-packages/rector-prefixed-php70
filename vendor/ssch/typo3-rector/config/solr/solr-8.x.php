<?php

declare (strict_types=1);
namespace RectorPrefix20210523;

use RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $containerConfigurator->import(__DIR__ . '/v8/*');
};
