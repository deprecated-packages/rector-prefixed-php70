<?php

declare (strict_types=1);
namespace PHPStan\Analyser\ResultCache;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Finder\Finder;
class ResultCacheClearer
{
    /** @var string */
    private $cacheFilePath;
    /** @var string */
    private $tempResultCachePath;
    public function __construct(string $cacheFilePath, string $tempResultCachePath)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->tempResultCachePath = $tempResultCachePath;
    }
    public function clear() : string
    {
        $dir = \dirname($this->cacheFilePath);
        if (!\is_file($this->cacheFilePath)) {
            return $dir;
        }
        @\unlink($this->cacheFilePath);
        return $dir;
    }
    /**
     * @return void
     */
    public function clearTemporaryCaches()
    {
        $finder = new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Finder\Finder();
        foreach ($finder->files()->name('*.php')->in($this->tempResultCachePath) as $tmpResultCacheFile) {
            @\unlink($tmpResultCacheFile->getPathname());
        }
    }
}
