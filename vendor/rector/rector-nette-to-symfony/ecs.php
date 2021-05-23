<?php

declare (strict_types=1);
namespace RectorPrefix20210523;

use RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210523\Symplify\EasyCodingStandard\ValueObject\Option;
use RectorPrefix20210523\Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (\RectorPrefix20210523\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(\RectorPrefix20210523\Symplify\EasyCodingStandard\ValueObject\Set\SetList::COMMON);
    $containerConfigurator->import(\RectorPrefix20210523\Symplify\EasyCodingStandard\ValueObject\Set\SetList::PSR_12);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20210523\Symplify\EasyCodingStandard\ValueObject\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
};
