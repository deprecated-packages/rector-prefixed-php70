<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\Core\Contract\Console\OutputStyleInterface;
use RectorPrefix20210522\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * This services helps to abstract from Symfony, and allow custom output formatters to use this Rector internal class.
 * It is very helpful while scoping Rector from analysed project.
 */
final class RectorOutputStyle implements \Rector\Core\Contract\Console\OutputStyleInterface
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\RectorPrefix20210522\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
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
    public function success(string $message)
    {
        $this->symfonyStyle->success($message);
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
    public function title(string $message)
    {
        $this->symfonyStyle->title($message);
    }
    /**
     * @return void
     */
    public function writeln(string $message)
    {
        $this->symfonyStyle->writeln($message);
    }
    /**
     * @return void
     */
    public function newline(int $count = 1)
    {
        $this->symfonyStyle->newLine($count);
    }
    /**
     * @param string[] $elements
     * @return void
     */
    public function listing(array $elements)
    {
        $this->symfonyStyle->listing($elements);
    }
}
