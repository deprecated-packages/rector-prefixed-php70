<?php

declare (strict_types=1);
namespace RectorPrefix20210504\Symplify\SetConfigResolver\Tests\Bootstrap;

use RectorPrefix20210504\PHPUnit\Framework\TestCase;
use RectorPrefix20210504\Symplify\SetConfigResolver\Bootstrap\InvalidSetReporter;
use RectorPrefix20210504\Symplify\SetConfigResolver\Exception\SetNotFoundException;
final class InvalidSetReporterTest extends \RectorPrefix20210504\PHPUnit\Framework\TestCase
{
    /**
     * @var InvalidSetReporter
     */
    private $invalidSetReporter;
    protected function setUp()
    {
        $this->invalidSetReporter = new \RectorPrefix20210504\Symplify\SetConfigResolver\Bootstrap\InvalidSetReporter();
    }
    /**
     * @doesNotPerformAssertions
     */
    public function test()
    {
        $setNotFoundException = new \RectorPrefix20210504\Symplify\SetConfigResolver\Exception\SetNotFoundException('not found', 'one', ['two', 'three']);
        $this->invalidSetReporter->report($setNotFoundException);
    }
}
