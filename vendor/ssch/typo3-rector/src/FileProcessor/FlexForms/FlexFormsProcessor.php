<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\FlexForms;

use DOMDocument;
use Exception;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface;
use UnexpectedValueException;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\FlexForms\FlexFormsProcessorTest
 */
final class FlexFormsProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var mixed[]
     */
    private $flexFormRectors;
    /**
     * @param FlexFormRectorInterface[] $flexFormRectors
     */
    public function __construct(array $flexFormRectors)
    {
        $this->flexFormRectors = $flexFormRectors;
    }
    /**
     * @param File[] $files
     * @return void
     */
    public function process(array $files)
    {
        if ([] === $this->flexFormRectors) {
            return;
        }
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if (!\in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true)) {
            return \false;
        }
        $fileContent = $file->getFileContent();
        try {
            $xml = @\simplexml_load_string($fileContent);
        } catch (\Exception $exception) {
            return \false;
        }
        if (\false === $xml) {
            return \false;
        }
        return 'T3DataStructure' === $xml->getName();
    }
    public function getSupportedFileExtensions() : array
    {
        return ['xml'];
    }
    /**
     * @return void
     */
    private function processFile(\Rector\Core\ValueObject\Application\File $file)
    {
        $domDocument = new \DOMDocument();
        $domDocument->formatOutput = \true;
        $domDocument->loadXML($file->getFileContent());
        $hasChanged = \false;
        foreach ($this->flexFormRectors as $flexFormRector) {
            $hasChanged = $flexFormRector->transform($domDocument);
        }
        if (!$hasChanged) {
            return;
        }
        $xml = $domDocument->saveXML($domDocument->documentElement, \LIBXML_NOEMPTYTAG);
        if (\false === $xml) {
            throw new \UnexpectedValueException('Could not convert to xml');
        }
        if ($xml === $file->getFileContent()) {
            return;
        }
        $newFileContent = \html_entity_decode($xml);
        $file->changeFileContent($newFileContent);
    }
}
