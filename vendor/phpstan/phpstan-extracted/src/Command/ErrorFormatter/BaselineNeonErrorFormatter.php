<?php

declare (strict_types=1);
namespace PHPStan\Command\ErrorFormatter;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\Output;
use PHPStan\File\RelativePathHelper;
use const SORT_STRING;
use function ksort;
use function preg_quote;
class BaselineNeonErrorFormatter implements \PHPStan\Command\ErrorFormatter\ErrorFormatter
{
    /** @var \PHPStan\File\RelativePathHelper */
    private $relativePathHelper;
    public function __construct(\PHPStan\File\RelativePathHelper $relativePathHelper)
    {
        $this->relativePathHelper = $relativePathHelper;
    }
    public function formatErrors(\PHPStan\Command\AnalysisResult $analysisResult, \PHPStan\Command\Output $output) : int
    {
        if (!$analysisResult->hasErrors()) {
            $output->writeRaw(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::encode(['parameters' => ['ignoreErrors' => []]], \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::BLOCK));
            return 0;
        }
        $fileErrors = [];
        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            if (!$fileSpecificError->canBeIgnored()) {
                continue;
            }
            $fileErrors[$fileSpecificError->getFilePath()][] = $fileSpecificError->getMessage();
        }
        \ksort($fileErrors, \SORT_STRING);
        $errorsToOutput = [];
        foreach ($fileErrors as $file => $errorMessages) {
            $fileErrorsCounts = [];
            foreach ($errorMessages as $errorMessage) {
                if (!isset($fileErrorsCounts[$errorMessage])) {
                    $fileErrorsCounts[$errorMessage] = 1;
                    continue;
                }
                $fileErrorsCounts[$errorMessage]++;
            }
            \ksort($fileErrorsCounts, \SORT_STRING);
            foreach ($fileErrorsCounts as $message => $count) {
                $errorsToOutput[] = ['message' => \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::escape('#^' . \preg_quote($message, '#') . '$#'), 'count' => $count, 'path' => \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::escape($this->relativePathHelper->getRelativePath($file))];
            }
        }
        $output->writeRaw(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::encode(['parameters' => ['ignoreErrors' => $errorsToOutput]], \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::BLOCK));
        return 1;
    }
}
