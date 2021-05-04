<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\Skipper\Tests\FileSystem;

use Iterator;
use RectorPrefix20210504\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210504\Symplify\Skipper\FileSystem\PathNormalizer;
use RectorPrefix20210504\Symplify\Skipper\HttpKernel\SkipperKernel;
final class PathNormalizerTest extends \RectorPrefix20210504\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    /**
     * @var PathNormalizer
     */
    private $pathNormalizer;
    protected function setUp()
    {
        $this->bootKernel(\RectorPrefix20210504\Symplify\Skipper\HttpKernel\SkipperKernel::class);
        $this->pathNormalizer = $this->getService(\RectorPrefix20210504\Symplify\Skipper\FileSystem\PathNormalizer::class);
    }
    /**
     * @dataProvider providePaths
     */
    public function testPaths(string $path, string $expectedNormalizedPath)
    {
        $normalizedPath = $this->pathNormalizer->normalizeForFnmatch($path);
        $this->assertSame($expectedNormalizedPath, $normalizedPath);
    }
    public function providePaths() : \Iterator
    {
        (yield ['path/with/no/asterisk', 'path/with/no/asterisk']);
        (yield ['*path/with/asterisk/begin', '*path/with/asterisk/begin*']);
        (yield ['path/with/asterisk/end*', '*path/with/asterisk/end*']);
        (yield ['*path/with/asterisk/begin/and/end*', '*path/with/asterisk/begin/and/end*']);
        (yield [__DIR__ . '/Fixture/path/with/../in/it', __DIR__ . '/Fixture/path/in/it']);
        (yield [__DIR__ . '/Fixture/path/with/../../in/it', __DIR__ . '/Fixture/in/it']);
    }
}
