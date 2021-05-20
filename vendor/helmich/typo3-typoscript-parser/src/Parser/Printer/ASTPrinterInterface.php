<?php

declare (strict_types=1);
namespace RectorPrefix20210520\Helmich\TypoScriptParser\Parser\Printer;

use RectorPrefix20210520\Symfony\Component\Console\Output\OutputInterface;
interface ASTPrinterInterface
{
    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement[]  $statements
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function printStatements(array $statements, \RectorPrefix20210520\Symfony\Component\Console\Output\OutputInterface $output);
    /**
     * @param PrettyPrinterConfiguration $prettyPrinterConfiguration
     * @return void
     */
    public function setPrettyPrinterConfiguration(\RectorPrefix20210520\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration $prettyPrinterConfiguration);
}
