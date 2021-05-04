<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\SmartFileSystem\Tests\SmartFileSystem;

use RectorPrefix20210504\PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210504\Symplify\SmartFileSystem\SmartFileSystem;
final class SmartFileSystemTest extends \RectorPrefix20210504\PHPUnit\Framework\TestCase
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    protected function setUp()
    {
        $this->smartFileSystem = new \RectorPrefix20210504\Symplify\SmartFileSystem\SmartFileSystem();
    }
    public function testReadFileToSmartFileInfo()
    {
        $readFileToSmartFileInfo = $this->smartFileSystem->readFileToSmartFileInfo(__DIR__ . '/Source/file.txt');
        $this->assertInstanceof(\Symplify\SmartFileSystem\SmartFileInfo::class, $readFileToSmartFileInfo);
    }
}
