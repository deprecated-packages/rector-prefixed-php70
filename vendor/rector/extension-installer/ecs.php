<?php

declare (strict_types=1);
namespace RectorPrefix20210522;

use RectorPrefix20210522\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use RectorPrefix20210522\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Option;
use RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (\RectorPrefix20210522\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(\RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Set\SetList::PSR_12);
    $containerConfigurator->import(\RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Set\SetList::SYMPLIFY);
    $containerConfigurator->import(\RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Set\SetList::COMMON);
    $containerConfigurator->import(\RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Set\SetList::CLEAN_CODE);
    $services = $containerConfigurator->services();
    $services->set(\RectorPrefix20210522\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer::class)->call('configure', [['annotations' => ['throws', 'author', 'package', 'group', 'required', 'phpstan-ignore-line', 'phpstan-ignore-next-line']]]);
    $services->set(\RectorPrefix20210522\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer::class)->call('configure', [['allow_mixed' => \true]]);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20210522\Symplify\EasyCodingStandard\ValueObject\Option::PATHS, [__DIR__ . '/ecs.php', __DIR__ . '/src', __DIR__ . '/tests']);
};
