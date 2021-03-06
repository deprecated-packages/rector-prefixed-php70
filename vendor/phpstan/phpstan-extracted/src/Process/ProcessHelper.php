<?php

declare (strict_types=1);
namespace PHPStan\Process;

use PHPStan\Command\AnalyseCommand;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Input\InputInterface;
class ProcessHelper
{
    /**
     * @param string $mainScript
     * @param string $commandName
     * @param string|null $projectConfigFile
     * @param string[] $additionalItems
     * @param InputInterface $input
     * @return string
     */
    public static function getWorkerCommand(string $mainScript, string $commandName, $projectConfigFile, array $additionalItems, \RectorPrefix20210620\_HumbugBox15516bb2b566\Symfony\Component\Console\Input\InputInterface $input) : string
    {
        $processCommandArray = [\escapeshellarg(\PHP_BINARY)];
        if ($input->getOption('memory-limit') === null) {
            $processCommandArray[] = '-d';
            $processCommandArray[] = 'memory_limit=' . \ini_get('memory_limit');
        }
        foreach ([$mainScript, $commandName] as $arg) {
            $processCommandArray[] = \escapeshellarg($arg);
        }
        if ($projectConfigFile !== null) {
            $processCommandArray[] = '--configuration';
            $processCommandArray[] = \escapeshellarg($projectConfigFile);
        }
        $options = ['paths-file', \PHPStan\Command\AnalyseCommand::OPTION_LEVEL, 'autoload-file', 'memory-limit', 'xdebug'];
        foreach ($options as $optionName) {
            /** @var bool|string|null $optionValue */
            $optionValue = $input->getOption($optionName);
            if (\is_bool($optionValue)) {
                if ($optionValue === \true) {
                    $processCommandArray[] = \sprintf('--%s', $optionName);
                }
                continue;
            }
            if ($optionValue === null) {
                continue;
            }
            $processCommandArray[] = \sprintf('--%s=%s', $optionName, \escapeshellarg($optionValue));
        }
        $processCommandArray = \array_merge($processCommandArray, $additionalItems);
        $processCommandArray[] = '--';
        /** @var string[] $paths */
        $paths = $input->getArgument('paths');
        foreach ($paths as $path) {
            $processCommandArray[] = \escapeshellarg($path);
        }
        return \implode(' ', $processCommandArray);
    }
}
