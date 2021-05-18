<?php

declare (strict_types=1);
namespace PHPStan\Command\Symfony;

use PHPStan\Command\Output;
use PHPStan\Command\OutputStyle;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface;
/**
 * @internal
 */
class SymfonyOutput implements \PHPStan\Command\Output
{
    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $symfonyOutput;
    /** @var OutputStyle */
    private $style;
    public function __construct(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface $symfonyOutput, \PHPStan\Command\OutputStyle $style)
    {
        $this->symfonyOutput = $symfonyOutput;
        $this->style = $style;
    }
    /**
     * @return void
     */
    public function writeFormatted(string $message)
    {
        $this->symfonyOutput->write($message, \false, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface::OUTPUT_NORMAL);
    }
    /**
     * @return void
     */
    public function writeLineFormatted(string $message)
    {
        $this->symfonyOutput->writeln($message, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface::OUTPUT_NORMAL);
    }
    /**
     * @return void
     */
    public function writeRaw(string $message)
    {
        $this->symfonyOutput->write($message, \false, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW);
    }
    public function getStyle() : \PHPStan\Command\OutputStyle
    {
        return $this->style;
    }
    public function isVerbose() : bool
    {
        return $this->symfonyOutput->isVerbose();
    }
    public function isDebug() : bool
    {
        return $this->symfonyOutput->isDebug();
    }
}
