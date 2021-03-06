<?php

declare (strict_types=1);
namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameNonPhpTest
 */
final class NonPhpFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var mixed[]
     */
    private $nonPhpRectors;
    /**
     * @param NonPhpRectorInterface[] $nonPhpRectors
     */
    public function __construct(array $nonPhpRectors)
    {
        $this->nonPhpRectors = $nonPhpRectors;
    }
    /**
     * @param File[] $files
     * @return void
     */
    public function process(array $files)
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $supportedFileExtension) {
            if (\substr_compare($smartFileInfo->getPathname(), '.' . $supportedFileExtension, -\strlen('.' . $supportedFileExtension)) === 0) {
                return \true;
            }
        }
        return \false;
    }
    public function getSupportedFileExtensions() : array
    {
        return \Rector\Core\ValueObject\StaticNonPhpFileSuffixes::SUFFIXES;
    }
    /**
     * @return void
     */
    private function processFile(\Rector\Core\ValueObject\Application\File $file)
    {
        foreach ($this->nonPhpRectors as $nonPhpRector) {
            $newFileContent = $nonPhpRector->refactorFileContent($file->getFileContent());
            $file->changeFileContent($newFileContent);
        }
    }
}
