<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\SmartFileSystem\Tests\Finder\FinderSanitizer;

use RectorPrefix20210504\Nette\Utils\Finder as NetteFinder;
use RectorPrefix20210504\Nette\Utils\Strings;
use RectorPrefix20210504\PHPUnit\Framework\TestCase;
use SplFileInfo;
use RectorPrefix20210504\Symfony\Component\Finder\Finder as SymfonyFinder;
use RectorPrefix20210504\Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use RectorPrefix20210504\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FinderSanitizerTest extends \RectorPrefix20210504\PHPUnit\Framework\TestCase
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;
    protected function setUp()
    {
        $this->finderSanitizer = new \RectorPrefix20210504\Symplify\SmartFileSystem\Finder\FinderSanitizer();
    }
    public function testValidTypes()
    {
        $files = [new \SplFileInfo(__DIR__ . '/Source/MissingFile.php')];
        $sanitizedFiles = $this->finderSanitizer->sanitize($files);
        $this->assertCount(0, $sanitizedFiles);
    }
    public function testSymfonyFinder()
    {
        $symfonyFinder = \RectorPrefix20210504\Symfony\Component\Finder\Finder::create()->files()->in(__DIR__ . '/Source');
        $fileInfos = \iterator_to_array($symfonyFinder->getIterator());
        $this->assertCount(2, $fileInfos);
        $files = $this->finderSanitizer->sanitize($symfonyFinder);
        $this->assertCount(2, $files);
        $this->assertFilesEqualFixtureFiles($files[0], $files[1]);
    }
    public function testNetteFinder()
    {
        $netteFinder = \RectorPrefix20210504\Nette\Utils\Finder::findFiles('*')->from(__DIR__ . '/Source');
        $fileInfos = \iterator_to_array($netteFinder->getIterator());
        $this->assertCount(2, $fileInfos);
        $files = $this->finderSanitizer->sanitize($netteFinder);
        $this->assertCount(2, $files);
        $this->assertFilesEqualFixtureFiles($files[0], $files[1]);
    }
    /**
     * On different OS the order of the two files can differ, only symfony finder would have a sort function, nette
     * finder does not. so we test if the correct files are there but ignore the order.
     */
    private function assertFilesEqualFixtureFiles(\Symplify\SmartFileSystem\SmartFileInfo $firstSmartFileInfo, \Symplify\SmartFileSystem\SmartFileInfo $secondSmartFileInfo)
    {
        $this->assertFileIsFromFixtureDirAndHasCorrectClass($firstSmartFileInfo);
        $this->assertFileIsFromFixtureDirAndHasCorrectClass($secondSmartFileInfo);
        // order agnostic file check
        $this->assertTrue(\RectorPrefix20210504\Nette\Utils\Strings::endsWith($firstSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/FileWithClass.php') && \RectorPrefix20210504\Nette\Utils\Strings::endsWith($secondSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/EmptyFile.php') || \RectorPrefix20210504\Nette\Utils\Strings::endsWith($firstSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/EmptyFile.php') && \RectorPrefix20210504\Nette\Utils\Strings::endsWith($secondSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/FileWithClass.php'));
    }
    private function assertFileIsFromFixtureDirAndHasCorrectClass(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo)
    {
        $this->assertInstanceOf(\RectorPrefix20210504\Symfony\Component\Finder\SplFileInfo::class, $smartFileInfo);
        $this->assertStringEndsWith('NestedDirectory', $smartFileInfo->getRelativeDirectoryPath());
    }
}
