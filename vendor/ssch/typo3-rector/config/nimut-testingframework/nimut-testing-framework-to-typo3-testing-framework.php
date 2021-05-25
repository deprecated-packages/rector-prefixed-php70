<?php

declare (strict_types=1);
namespace RectorPrefix20210525;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set('nimut_testing_framework_to_typo3_testing_framework')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['Nimut\\TestingFramework\\TestCase\\UnitTestCase' => 'TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'Nimut\\TestingFramework\\TestCase\\FunctionalTestCase' => 'TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase', 'Nimut\\TestingFramework\\TestCase\\ViewHelperBaseTestcase' => 'TYPO3\\TestingFramework\\Fluid\\Unit\\ViewHelpers\\ViewHelperBaseTestcase', 'Nimut\\TestingFramework\\MockObject\\AccessibleMockObjectInterface' => 'TYPO3\\TestingFramework\\Core\\AccessibleObjectInterface', 'Nimut\\TestingFramework\\Exception\\Exception' => 'TYPO3\\TestingFramework\\Core\\Exception']]]);
};
