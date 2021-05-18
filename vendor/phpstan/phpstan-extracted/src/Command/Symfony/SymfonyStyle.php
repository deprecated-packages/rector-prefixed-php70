<?php

declare (strict_types=1);
namespace PHPStan\Command\Symfony;

use PHPStan\Command\OutputStyle;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Style\StyleInterface;
/**
 * @internal
 */
class SymfonyStyle implements \PHPStan\Command\OutputStyle
{
    /** @var \Symfony\Component\Console\Style\StyleInterface */
    private $symfonyStyle;
    public function __construct(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Style\StyleInterface $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function getSymfonyStyle() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Style\StyleInterface
    {
        return $this->symfonyStyle;
    }
    /**
     * @return void
     */
    public function title(string $message)
    {
        $this->symfonyStyle->title($message);
    }
    /**
     * @return void
     */
    public function section(string $message)
    {
        $this->symfonyStyle->section($message);
    }
    /**
     * @return void
     */
    public function listing(array $elements)
    {
        $this->symfonyStyle->listing($elements);
    }
    /**
     * @return void
     */
    public function success(string $message)
    {
        $this->symfonyStyle->success($message);
    }
    /**
     * @return void
     */
    public function error(string $message)
    {
        $this->symfonyStyle->error($message);
    }
    /**
     * @return void
     */
    public function warning(string $message)
    {
        $this->symfonyStyle->warning($message);
    }
    /**
     * @return void
     */
    public function note(string $message)
    {
        $this->symfonyStyle->note($message);
    }
    /**
     * @return void
     */
    public function caution(string $message)
    {
        $this->symfonyStyle->caution($message);
    }
    /**
     * @return void
     */
    public function table(array $headers, array $rows)
    {
        $this->symfonyStyle->table($headers, $rows);
    }
    /**
     * @return void
     */
    public function newLine(int $count = 1)
    {
        $this->symfonyStyle->newLine($count);
    }
    /**
     * @return void
     */
    public function progressStart(int $max = 0)
    {
        $this->symfonyStyle->progressStart($max);
    }
    /**
     * @return void
     */
    public function progressAdvance(int $step = 1)
    {
        $this->symfonyStyle->progressAdvance($step);
    }
    /**
     * @return void
     */
    public function progressFinish()
    {
        $this->symfonyStyle->progressFinish();
    }
}
