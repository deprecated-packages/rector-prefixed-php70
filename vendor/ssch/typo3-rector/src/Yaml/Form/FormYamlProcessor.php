<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Yaml\Form;

use RectorPrefix20210522\Nette\Utils\Strings;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\Yaml\Form\FormYamlRectorInterface;
use RectorPrefix20210522\Symfony\Component\Yaml\Yaml;
/**
 * @see \Ssch\TYPO3Rector\Tests\Yaml\Form\FormYamlProcessorTest
 */
final class FormYamlProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var string[]
     */
    const ALLOWED_FILE_EXTENSIONS = ['yaml'];
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var mixed[]
     */
    private $transformer;
    /**
     * @param FormYamlRectorInterface[] $transformer
     */
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, array $transformer)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->transformer = $transformer;
    }
    /**
     * @param File[] $files
     * @return void
     */
    public function process(array $files)
    {
        // Prevent unnecessary processing
        if ([] === $this->transformer) {
            return;
        }
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \RectorPrefix20210522\Nette\Utils\Strings::endsWith($smartFileInfo->getFilename(), 'form.yaml');
    }
    public function getSupportedFileExtensions() : array
    {
        return self::ALLOWED_FILE_EXTENSIONS;
    }
    /**
     * @return void
     */
    private function processFile(\Rector\Core\ValueObject\Application\File $file)
    {
        $this->currentFileProvider->setFile($file);
        $smartFileInfo = $file->getSmartFileInfo();
        $yaml = \RectorPrefix20210522\Symfony\Component\Yaml\Yaml::parseFile($smartFileInfo->getRealPath());
        if (!\is_array($yaml)) {
            return;
        }
        $newYaml = $yaml;
        foreach ($this->transformer as $transformer) {
            $newYaml = $transformer->refactor($newYaml);
        }
        // Nothing has changed. Early return here.
        if ($newYaml === $yaml) {
            return;
        }
        $newFileContent = \RectorPrefix20210522\Symfony\Component\Yaml\Yaml::dump($newYaml, 99);
        $file->changeFileContent($newFileContent);
    }
}
