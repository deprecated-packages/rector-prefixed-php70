<?php

namespace RectorPrefix20210526\TYPO3\CMS\Core\Localization;

use RectorPrefix20210526\TYPO3\CMS\Core\Site\Entity\SiteLanguage;
if (\class_exists('TYPO3\\CMS\\Core\\Localization\\Locales')) {
    return;
}
class Locales
{
    /**
     * @return void
     */
    public static function setSystemLocaleFromSiteLanguage(\RectorPrefix20210526\TYPO3\CMS\Core\Site\Entity\SiteLanguage $siteLanguage)
    {
    }
    /**
     * @return string
     */
    public function getPreferredClientLanguage($languageCodesList)
    {
        return 'foo';
    }
}
