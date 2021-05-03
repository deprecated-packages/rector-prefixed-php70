<?php

declare (strict_types=1);
namespace RectorPrefix20210503\Symplify\SetConfigResolver;

use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symplify\SetConfigResolver\Console\Option\OptionName;
use RectorPrefix20210503\Symplify\SetConfigResolver\Console\OptionValueResolver;
use RectorPrefix20210503\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
abstract class AbstractConfigResolver
{
    /**
     * @var OptionValueResolver
     */
    private $optionValueResolver;
    public function __construct()
    {
        $this->optionValueResolver = new \RectorPrefix20210503\Symplify\SetConfigResolver\Console\OptionValueResolver();
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    public function resolveFromInput(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input)
    {
        $configValue = $this->optionValueResolver->getOptionValue($input, \RectorPrefix20210503\Symplify\SetConfigResolver\Console\Option\OptionName::CONFIG);
        if ($configValue !== null) {
            if (!\file_exists($configValue)) {
                $message = \sprintf('File "%s" was not found', $configValue);
                throw new \RectorPrefix20210503\Symplify\SmartFileSystem\Exception\FileNotFoundException($message);
            }
            return $this->createFileInfo($configValue);
        }
        return null;
    }
    /**
     * @param string[] $fallbackFiles
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    public function resolveFromInputWithFallback(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, array $fallbackFiles)
    {
        $configFileInfo = $this->resolveFromInput($input);
        if ($configFileInfo !== null) {
            return $configFileInfo;
        }
        return $this->createFallbackFileInfoIfFound($fallbackFiles);
    }
    /**
     * @param string[] $fallbackFiles
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private function createFallbackFileInfoIfFound(array $fallbackFiles)
    {
        foreach ($fallbackFiles as $fallbackFile) {
            $rootFallbackFile = \getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
            if (\is_file($rootFallbackFile)) {
                return $this->createFileInfo($rootFallbackFile);
            }
        }
        return null;
    }
    private function createFileInfo(string $configValue) : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return new \Symplify\SmartFileSystem\SmartFileInfo($configValue);
    }
}
