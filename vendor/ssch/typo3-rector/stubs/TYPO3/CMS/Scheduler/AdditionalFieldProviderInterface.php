<?php

declare (strict_types=1);
namespace RectorPrefix20210616\TYPO3\CMS\Scheduler;

use RectorPrefix20210616\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
if (\interface_exists('TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
    return;
}
interface AdditionalFieldProviderInterface
{
    public function getAdditionalFields(array &$taskInfo, $task, \RectorPrefix20210616\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule);
}
