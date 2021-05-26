<?php

declare (strict_types=1);
namespace RectorPrefix20210526;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector;
use RectorPrefix20210526\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210526\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\Extensions\solr\v9\ApacheSolrDocumentToSolariumDocumentRector::class);
    $services->set('apache_solr_to_solarium_classes')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['Apache_Solr_Document' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\Document\\Document', 'Apache_Solr_Response' => 'ApacheSolrForTypo3\\Solr\\System\\Solr\\ResponseAdapter']]]);
};
