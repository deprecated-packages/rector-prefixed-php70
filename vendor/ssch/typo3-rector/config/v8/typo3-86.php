<?php

declare (strict_types=1);
namespace RectorPrefix20210616;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210616\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set('namespace_typo3_cms_core_tests_to__typo3_testing_framework_core')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['TYPO3\\CMS\\Core\\Tests\\UnitTestCase' => 'TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'TYPO3\\CMS\\Core\\Tests\\FunctionalTestCase' => 'TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase']]]);
};
