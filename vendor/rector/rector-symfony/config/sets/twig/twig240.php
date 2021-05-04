<?php

declare (strict_types=1);
namespace RectorPrefix20210504;

use RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210504\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/twig-underscore-to-namespace.php');
};
