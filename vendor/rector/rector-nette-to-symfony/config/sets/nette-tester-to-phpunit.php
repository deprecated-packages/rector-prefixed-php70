<?php

declare (strict_types=1);
namespace RectorPrefix20210522;

use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Rector\Class_\RenameTesterTestToPHPUnitToTestFileRector;
use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(\Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector::class);
    $services->set(\Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector::class);
    $services->set(\Rector\NetteTesterToPHPUnit\Rector\Class_\RenameTesterTestToPHPUnitToTestFileRector::class);
};
