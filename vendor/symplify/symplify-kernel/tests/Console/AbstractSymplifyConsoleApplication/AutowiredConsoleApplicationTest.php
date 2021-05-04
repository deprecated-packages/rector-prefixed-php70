<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\SymplifyKernel\Tests\Console\AbstractSymplifyConsoleApplication;

use RectorPrefix20210504\Symfony\Component\Console\Application;
use RectorPrefix20210504\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210504\Symplify\SymplifyKernel\Tests\HttpKernel\OnlyForTestsKernel;
final class AutowiredConsoleApplicationTest extends \RectorPrefix20210504\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    protected function setUp()
    {
        $this->bootKernel(\RectorPrefix20210504\Symplify\SymplifyKernel\Tests\HttpKernel\OnlyForTestsKernel::class);
    }
    public function test()
    {
        $application = $this->getService(\RectorPrefix20210504\Symfony\Component\Console\Application::class);
        $this->assertInstanceOf(\RectorPrefix20210504\Symfony\Component\Console\Application::class, $application);
    }
}
