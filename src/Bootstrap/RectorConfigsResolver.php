<?php

declare (strict_types=1);
namespace Rector\Core\Bootstrap;

use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorConfigsResolver
{
    public function provide() : \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs
    {
        $argvInput = new \RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput();
        $mainConfigFileInfo = $this->resolveFromInputWithFallback($argvInput, 'rector.php');
        $rectorRecipeConfigFileInfo = $this->resolveRectorRecipeConfig($argvInput);
        $configFileInfos = [];
        if ($rectorRecipeConfigFileInfo !== null) {
            $configFileInfos[] = $rectorRecipeConfigFileInfo;
        }
        return new \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs($mainConfigFileInfo, $configFileInfos);
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private function resolveRectorRecipeConfig(\RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput $argvInput)
    {
        if ($argvInput->getFirstArgument() !== 'generate') {
            return null;
        }
        // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
        $rectorRecipeFilePath = \getcwd() . '/rector-recipe.php';
        if (!\file_exists($rectorRecipeFilePath)) {
            return null;
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($rectorRecipeFilePath);
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private function resolveFromInput(\RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput $argvInput)
    {
        $configValue = $this->getOptionValue($argvInput, ['--config', '-c']);
        if ($configValue === null) {
            return null;
        }
        if (!\file_exists($configValue)) {
            $message = \sprintf('File "%s" was not found', $configValue);
            throw new \Symplify\SmartFileSystem\Exception\FileNotFoundException($message);
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($configValue);
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private function resolveFromInputWithFallback(\RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput $argvInput, string $fallbackFile)
    {
        $configFileInfo = $this->resolveFromInput($argvInput);
        if ($configFileInfo !== null) {
            return $configFileInfo;
        }
        return $this->createFallbackFileInfoIfFound($fallbackFile);
    }
    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private function createFallbackFileInfoIfFound(string $fallbackFile)
    {
        $rootFallbackFile = \getcwd() . \DIRECTORY_SEPARATOR . $fallbackFile;
        if (!\is_file($rootFallbackFile)) {
            return null;
        }
        return new \Symplify\SmartFileSystem\SmartFileInfo($rootFallbackFile);
    }
    /**
     * @param string[] $optionNames
     * @return string|null
     */
    private function getOptionValue(\RectorPrefix20210525\Symfony\Component\Console\Input\ArgvInput $argvInput, array $optionNames)
    {
        foreach ($optionNames as $optionName) {
            if ($argvInput->hasParameterOption($optionName, \true)) {
                return $argvInput->getParameterOption($optionName, null, \true);
            }
        }
        return null;
    }
}
