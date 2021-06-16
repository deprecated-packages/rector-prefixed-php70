<?php

declare (strict_types=1);
namespace PHPStan\Command;

/** @api */
interface Output
{
    /**
     * @return void
     */
    public function writeFormatted(string $message);
    /**
     * @return void
     */
    public function writeLineFormatted(string $message);
    /**
     * @return void
     */
    public function writeRaw(string $message);
    public function getStyle() : \PHPStan\Command\OutputStyle;
    public function isVerbose() : bool;
    public function isDebug() : bool;
}
