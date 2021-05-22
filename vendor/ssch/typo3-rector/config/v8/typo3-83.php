<?php

declare (strict_types=1);
namespace RectorPrefix20210522;

use Ssch\TYPO3Rector\Rector\v8\v3\RefactorMethodFileContentRector;
use Ssch\TYPO3Rector\Rector\v8\v3\RefactorQueryViewTableWrapRector;
use Ssch\TYPO3Rector\Resources\Icons\IconsProcessor;
use Ssch\TYPO3Rector\Resources\Icons\Rector\IconsRector;
use RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v3\RefactorMethodFileContentRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v3\RefactorQueryViewTableWrapRector::class);
    $services->set(\Ssch\TYPO3Rector\Resources\Icons\Rector\IconsRector::class);
    $services->set(\Ssch\TYPO3Rector\Resources\Icons\IconsProcessor::class)->autowire();
};
