<?php

declare (strict_types=1);
namespace Rector\Core\Application\FileProcessor;

use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20210503\Symfony\Component\Console\Helper\ProgressBar;
use RectorPrefix20210503\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210503\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Throwable;
final class PhpFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * Why 4? One for each cycle, so user sees some activity all the time:
     *
     * 1) parsing files
     * 2) main rectoring
     * 3) post-rectoring (removing files, importing names)
     * 4) printing
     *
     * @var int
     */
    const PROGRESS_BAR_STEP_MULTIPLIER = 4;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var File[]
     */
    private $notParsedFiles = [];
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var FileProcessor
     */
    private $fileProcessor;
    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    /**
     * @var RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;
    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @var FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;
    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;
    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;
    /**
     * @var ErrorFactory
     */
    private $errorFactory;
    public function __construct(\Rector\Core\Configuration\Configuration $configuration, \Rector\Core\PhpParser\Printer\FormatPerservingPrinter $formatPerservingPrinter, \Rector\Core\Application\FileProcessor $fileProcessor, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor, \RectorPrefix20210503\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \RectorPrefix20210503\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\PostRector\Application\PostFileProcessor $postFileProcessor, \Rector\ChangesReporting\ValueObjectFactory\ErrorFactory $errorFactory)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->privatesAccessor = $privatesAccessor;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->configuration = $configuration;
        $this->currentFileProvider = $currentFileProvider;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->postFileProcessor = $postFileProcessor;
        $this->errorFactory = $errorFactory;
    }
    /**
     * @param File[] $files
     * @return void
     */
    public function process(array $files)
    {
        $fileCount = \count($files);
        if ($fileCount === 0) {
            return;
        }
        $this->prepareProgressBar($fileCount);
        // 1. parse files to nodes
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) {
                $this->fileProcessor->parseFileInfoToLocalCache($file);
            }, 'parsing');
        }
        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($files);
        // 3. apply post rectors
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) {
                $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());
                // this is needed for new tokens added in "afterTraverse()"
                $file->changeNewStmts($newStmts);
            }, 'post rectors');
        }
        // 4. print to file or string
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);
            // cannot print file with errors, as print would break everything to original nodes
            if ($file->hasErrors()) {
                $this->advance($file, 'printing skipped due error');
                continue;
            }
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) {
                $this->printFile($file);
            }, 'printing');
        }
        if ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->newLine(2);
        }
        // 4. remove and add files
        $this->removedAndAddedFilesProcessor->run();
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return $this->configuration->getFileExtensions();
    }
    /**
     * @return void
     */
    private function prepareProgressBar(int $fileCount)
    {
        if ($this->symfonyStyle->isVerbose()) {
            return;
        }
        if (!$this->configuration->shouldShowProgressBar()) {
            return;
        }
        $this->configureStepCount($fileCount);
    }
    /**
     * @param File[] $files
     * @return void
     */
    private function refactorNodesWithRectors(array $files)
    {
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) {
                $this->fileProcessor->refactor($file);
            }, 'refactoring');
        }
    }
    /**
     * @return void
     */
    private function tryCatchWrapper(\Rector\Core\ValueObject\Application\File $file, callable $callback, string $phase)
    {
        $this->currentFileProvider->setFile($file);
        $this->advance($file, $phase);
        try {
            if (\in_array($file, $this->notParsedFiles, \true)) {
                // we cannot process this file
                return;
            }
            $callback($file);
        } catch (\PHPStan\AnalysedCodeException $analysedCodeException) {
            $this->notParsedFiles[] = $file;
            $error = $this->errorFactory->createAutoloadError($analysedCodeException);
            $file->addRectorError($error);
        } catch (\Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || \Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }
            $rectorError = new \Rector\Core\ValueObject\Application\RectorError($throwable->getMessage(), $throwable->getLine());
            $file->addRectorError($rectorError);
        }
    }
    /**
     * @return void
     */
    private function printFile(\Rector\Core\ValueObject\Application\File $file)
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo)) {
            // skip, because this file exists no more
            return;
        }
        $newContent = $this->configuration->isDryRun() ? $this->formatPerservingPrinter->printParsedStmstAndTokensToString($file) : $this->formatPerservingPrinter->printParsedStmstAndTokens($file);
        $file->changeFileContent($newContent);
        $this->fileDiffFileDecorator->decorate([$file]);
    }
    /**
     * This prevent CI report flood with 1 file = 1 line in progress bar
     * @return void
     */
    private function configureStepCount(int $fileCount)
    {
        $this->symfonyStyle->progressStart($fileCount * self::PROGRESS_BAR_STEP_MULTIPLIER);
        $progressBar = $this->privatesAccessor->getPrivateProperty($this->symfonyStyle, 'progressBar');
        if (!$progressBar instanceof \RectorPrefix20210503\Symfony\Component\Console\Helper\ProgressBar) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($progressBar->getMaxSteps() < 40) {
            return;
        }
        $redrawFrequency = (int) ($progressBar->getMaxSteps() / 20);
        $progressBar->setRedrawFrequency($redrawFrequency);
    }
    /**
     * @return void
     */
    private function advance(\Rector\Core\ValueObject\Application\File $file, string $phase)
    {
        if ($this->symfonyStyle->isVerbose()) {
            $smartFileInfo = $file->getSmartFileInfo();
            $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(\getcwd());
            $message = \sprintf('[%s] %s', $phase, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
        } elseif ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->progressAdvance();
        }
    }
}
