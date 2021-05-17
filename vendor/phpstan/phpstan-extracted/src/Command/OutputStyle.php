<?php

declare (strict_types=1);
namespace PHPStan\Command;

interface OutputStyle
{
    /**
     * @return void
     */
    public function title(string $message);
    /**
     * @return void
     */
    public function section(string $message);
    /**
     * @param string[] $elements
     * @return void
     */
    public function listing(array $elements);
    /**
     * @return void
     */
    public function success(string $message);
    /**
     * @return void
     */
    public function error(string $message);
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
    public function caution(string $message);
    /**
     * @param mixed[] $headers
     * @param mixed[] $rows
     * @return void
     */
    public function table(array $headers, array $rows);
    /**
     * @return void
     */
    public function newLine(int $count = 1);
    /**
     * @return void
     */
    public function progressStart(int $max = 0);
    /**
     * @return void
     */
    public function progressAdvance(int $step = 1);
    /**
     * @return void
     */
    public function progressFinish();
}
