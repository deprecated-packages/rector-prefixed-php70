<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use RectorPrefix20210517\Composer\InstalledVersions;
use RectorPrefix20210517\Nette\Utils\Strings;
/**
 * Inspired by https://github.com/symplify/symplify/pull/3179/files
 * Local resolver is needed, because PHPStan is unprefixing its InstalledVersion classes and the API is changing way too often.
 * This makes sure it works without dependency on external conditions.
 */
final class VersionResolver
{
    public function resolve() : string
    {
        // give local IntalledVersions a priority above anything else
        $intalledVersionsFilepath = __DIR__ . '/../../vendor/composer/InstalledVersions.php';
        if (\file_exists($intalledVersionsFilepath)) {
            require_once $intalledVersionsFilepath;
        }
        $installedRawData = \RectorPrefix20210517\Composer\InstalledVersions::getRawData();
        $rectorPackageData = $this->resolvePackageData($installedRawData);
        if ($rectorPackageData === null) {
            return 'Unknown';
        }
        if (isset($rectorPackageData['replaced'])) {
            return 'replaced@' . $rectorPackageData['replaced'][0];
        }
        if ($rectorPackageData['version'] === 'dev-main') {
            $reference = $rectorPackageData['reference'] ?? null;
            if ($reference === null) {
                return 'dev-main';
            }
            return 'dev-main@' . \RectorPrefix20210517\Nette\Utils\Strings::substring($rectorPackageData['reference'], 0, 7);
        }
        return $rectorPackageData['version'];
    }
    /**
     * @param mixed[] $installedRawData
     * @return mixed[]|null
     */
    private function resolvePackageData(array $installedRawData)
    {
        return $installedRawData['versions']['rector/rector-src'] ?? $installedRawData['versions']['rector/rector'] ?? $installedRawData['root'] ?? null;
    }
}
