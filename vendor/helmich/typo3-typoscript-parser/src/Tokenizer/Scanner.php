<?php

declare (strict_types=1);
namespace RectorPrefix20210527\Helmich\TypoScriptParser\Tokenizer;

use Iterator;
/**
 * Helper class for scanning lines
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class Scanner implements \Iterator
{
    /** @var string[] */
    private $lines = [];
    /** @var int */
    private $index = 0;
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }
    public function current() : \RectorPrefix20210527\Helmich\TypoScriptParser\Tokenizer\ScannerLine
    {
        return new \RectorPrefix20210527\Helmich\TypoScriptParser\Tokenizer\ScannerLine($this->index + 1, $this->lines[$this->index]);
    }
    /**
     * @return void
     */
    public function next()
    {
        $this->index++;
    }
    public function key() : int
    {
        return $this->index;
    }
    public function valid() : bool
    {
        return $this->index < \count($this->lines);
    }
    /**
     * @return void
     */
    public function rewind()
    {
        $this->index = 0;
    }
}
