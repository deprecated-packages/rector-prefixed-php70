<?php

declare (strict_types=1);
namespace RectorPrefix20210531;

use Rector\Core\Configuration\Option;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use RectorPrefix20210531\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210531\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/config.php');
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH, \Ssch\TYPO3Rector\Configuration\Typo3Option::PHPSTAN_FOR_RECTOR_PATH);
};
