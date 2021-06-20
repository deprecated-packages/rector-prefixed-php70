<?php

declare (strict_types=1);
namespace RectorPrefix20210620;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $containerConfigurator->import(__DIR__ . '/v9/*');
};
