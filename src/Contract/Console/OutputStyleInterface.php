<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Console;

interface OutputStyleInterface
{
    /**
     * @return void
     */
    public function error(string $message);
    /**
     * @return void
     */
    public function success(string $message);
    /**
     * @return void
     */
    public function warning(string $message);
    /**
     * @return void
     */
    public function note(string $message);
    /**
     * @return void
     */
    public function title(string $message);
    /**
     * @return void
     */
    public function writeln(string $message);
    /**
     * @return void
     */
    public function newline(int $count = 1);
    /**
     * @param string[] $elements
     * @return void
     */
    public function listing(array $elements);
}
