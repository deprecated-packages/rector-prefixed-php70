<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use PHPStan\File\FileHelper;
class NeonLoader extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Config\Loader
{
    /** @var FileHelper */
    private $fileHelper;
    /** @var string|null */
    private $generateBaselineFile;
    /**
     * @param string|null $generateBaselineFile
     */
    public function __construct(\PHPStan\File\FileHelper $fileHelper, $generateBaselineFile)
    {
        $this->fileHelper = $fileHelper;
        $this->generateBaselineFile = $generateBaselineFile;
    }
    /**
     * @param string $file
     * @param bool|null $merge
     * @return mixed[]
     */
    public function load(string $file, $merge = \true) : array
    {
        if ($this->generateBaselineFile === null) {
            return parent::load($file, $merge);
        }
        $normalizedFile = $this->fileHelper->normalizePath($file);
        if ($this->generateBaselineFile === $normalizedFile) {
            return [];
        }
        return parent::load($file, $merge);
    }
}
