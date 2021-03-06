<?php

declare (strict_types=1);
namespace PHPStan\Php;

use const PHP_VERSION_ID;
class PhpVersionFactory
{
    /** @var int|null */
    private $versionId;
    /** @var string|null */
    private $composerPhpVersion;
    /**
     * @param int|null $versionId
     * @param string|null $composerPhpVersion
     */
    public function __construct($versionId, $composerPhpVersion)
    {
        $this->versionId = $versionId;
        $this->composerPhpVersion = $composerPhpVersion;
    }
    public function create() : \PHPStan\Php\PhpVersion
    {
        $versionId = $this->versionId;
        if ($versionId === null && $this->composerPhpVersion !== null) {
            $parts = \explode('.', $this->composerPhpVersion);
            $tmp = (int) $parts[0] * 10000 + (int) ($parts[1] ?? 0) * 100 + (int) ($parts[2] ?? 0);
            $tmp = \max($tmp, 70100);
            $versionId = \min($tmp, 80099);
        }
        if ($versionId === null) {
            $versionId = \PHP_VERSION_ID;
        }
        return new \PHPStan\Php\PhpVersion($versionId);
    }
}
