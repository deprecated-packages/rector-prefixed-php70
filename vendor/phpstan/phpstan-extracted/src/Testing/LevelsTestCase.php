<?php

declare (strict_types=1);
namespace PHPStan\Testing;

use PHPStan\File\FileHelper;
use PHPStan\File\FileWriter;
abstract class LevelsTestCase extends \RectorPrefix20210528\PHPUnit\Framework\TestCase
{
    /**
     * @return array<array<string>>
     */
    public abstract function dataTopics() : array;
    public abstract function getDataPath() : string;
    public abstract function getPhpStanExecutablePath() : string;
    /**
     * @return string|null
     */
    public abstract function getPhpStanConfigPath();
    protected function getResultSuffix() : string
    {
        return '';
    }
    protected function shouldAutoloadAnalysedFile() : bool
    {
        return \true;
    }
    /**
     * @dataProvider dataTopics
     * @param string $topic
     * @return void
     */
    public function testLevels(string $topic)
    {
        $file = \sprintf('%s' . \DIRECTORY_SEPARATOR . '%s.php', $this->getDataPath(), $topic);
        $command = \escapeshellcmd($this->getPhpStanExecutablePath());
        $configPath = $this->getPhpStanConfigPath();
        $fileHelper = new \PHPStan\File\FileHelper(__DIR__ . '/../..');
        $previousMessages = [];
        $exceptions = [];
        foreach (\range(0, 8) as $level) {
            unset($outputLines);
            \exec(\sprintf('%s %s clear-result-cache %s 2>&1', \escapeshellarg(\PHP_BINARY), $command, $configPath !== null ? '--configuration ' . \escapeshellarg($configPath) : ''), $clearResultCacheOutputLines, $clearResultCacheExitCode);
            if ($clearResultCacheExitCode !== 0) {
                throw new \PHPStan\ShouldNotHappenException('Could not clear result cache: ' . \implode("\n", $clearResultCacheOutputLines));
            }
            \exec(\sprintf('%s %s analyse --no-progress --error-format=prettyJson --level=%d %s %s %s', \escapeshellarg(\PHP_BINARY), $command, $level, $configPath !== null ? '--configuration ' . \escapeshellarg($configPath) : '', $this->shouldAutoloadAnalysedFile() ? \sprintf('--autoload-file %s', \escapeshellarg($file)) : '', \escapeshellarg($file)), $outputLines);
            $output = \implode("\n", $outputLines);
            try {
                $actualJson = \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::decode($output, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::FORCE_ARRAY);
            } catch (\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\JsonException $e) {
                throw new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\JsonException(\sprintf('Cannot decode: %s', $output));
            }
            if (\count($actualJson['files']) > 0) {
                $normalizedFilePath = $fileHelper->normalizePath($file);
                if (!isset($actualJson['files'][$normalizedFilePath])) {
                    $messagesBeforeDiffing = [];
                } else {
                    $messagesBeforeDiffing = $actualJson['files'][$normalizedFilePath]['messages'];
                }
                foreach ($this->getAdditionalAnalysedFiles() as $additionalAnalysedFile) {
                    $normalizedAdditionalFilePath = $fileHelper->normalizePath($additionalAnalysedFile);
                    if (!isset($actualJson['files'][$normalizedAdditionalFilePath])) {
                        continue;
                    }
                    $messagesBeforeDiffing = \array_merge($messagesBeforeDiffing, $actualJson['files'][$normalizedAdditionalFilePath]['messages']);
                }
            } else {
                $messagesBeforeDiffing = [];
            }
            $messages = [];
            foreach ($messagesBeforeDiffing as $message) {
                foreach ($previousMessages as $lastMessage) {
                    if ($message['message'] === $lastMessage['message'] && $message['line'] === $lastMessage['line']) {
                        continue 2;
                    }
                }
                $messages[] = $message;
            }
            $missingMessages = [];
            foreach ($previousMessages as $previousMessage) {
                foreach ($messagesBeforeDiffing as $message) {
                    if ($previousMessage['message'] === $message['message'] && $previousMessage['line'] === $message['line']) {
                        continue 2;
                    }
                }
                $missingMessages[] = $previousMessage;
            }
            $previousMessages = \array_merge($previousMessages, $messages);
            $expectedJsonFile = \sprintf('%s/%s-%d%s.json', $this->getDataPath(), $topic, $level, $this->getResultSuffix());
            $exception = $this->compareFiles($expectedJsonFile, $messages);
            if ($exception !== null) {
                $exceptions[] = $exception;
            }
            $expectedJsonMissingFile = \sprintf('%s/%s-%d-missing%s.json', $this->getDataPath(), $topic, $level, $this->getResultSuffix());
            $exception = $this->compareFiles($expectedJsonMissingFile, $missingMessages);
            if ($exception === null) {
                continue;
            }
            $exceptions[] = $exception;
        }
        if (\count($exceptions) > 0) {
            throw $exceptions[0];
        }
    }
    /**
     * @return string[]
     */
    public function getAdditionalAnalysedFiles() : array
    {
        return [];
    }
    /**
     * @param string $expectedJsonFile
     * @param string[] $expectedMessages
     * @return \PHPUnit\Framework\AssertionFailedError|null
     */
    private function compareFiles(string $expectedJsonFile, array $expectedMessages)
    {
        if (\count($expectedMessages) === 0) {
            try {
                self::assertFileDoesNotExist($expectedJsonFile);
                return null;
            } catch (\RectorPrefix20210528\PHPUnit\Framework\AssertionFailedError $e) {
                \unlink($expectedJsonFile);
                return $e;
            }
        }
        $actualOutput = \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::encode($expectedMessages, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Utils\Json::PRETTY);
        try {
            $this->assertJsonStringEqualsJsonFile($expectedJsonFile, $actualOutput);
        } catch (\RectorPrefix20210528\PHPUnit\Framework\AssertionFailedError $e) {
            \PHPStan\File\FileWriter::write($expectedJsonFile, $actualOutput);
            return $e;
        }
        return null;
    }
    /**
     * @return void
     */
    public static function assertFileDoesNotExist(string $filename, string $message = '')
    {
        if (!\method_exists(parent::class, 'assertFileDoesNotExist')) {
            parent::assertFileNotExists($filename, $message);
            return;
        }
        parent::assertFileDoesNotExist($filename, $message);
    }
}
