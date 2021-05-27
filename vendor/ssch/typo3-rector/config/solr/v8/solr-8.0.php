<?php

declare (strict_types=1);
namespace RectorPrefix20210527;

use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector;
use Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector;
use RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210527\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/../../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrConnectionAddDocumentsToWriteServiceAddDocumentsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\Extensions\solr\v8\SolrSiteToSolrRepositoryRector::class);
};
