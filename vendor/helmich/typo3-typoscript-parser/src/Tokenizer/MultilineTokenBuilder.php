<?php

declare (strict_types=1);
namespace RectorPrefix20210519\Helmich\TypoScriptParser\Tokenizer;

/**
 * Helper class for building tokens that span multiple lines.
 *
 * Examples are multi-line comments or "("-assignments.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class MultilineTokenBuilder
{
    /** @var string|null */
    private $type = null;
    /** @var string|null */
    private $value = null;
    /** @var int|null */
    private $startLine = null;
    /** @var int|null */
    private $startColumn = null;
    /**
     * @param string $type   Token type, one of `TokenInterface::TYPE_*`
     * @param string $value  Token value
     * @param int    $line   Starting line in source code
     * @param int    $column Starting column in source code
     * @return void
     */
    public function startMultilineToken(string $type, string $value, int $line, int $column)
    {
        $this->type = $type;
        $this->value = $value;
        $this->startLine = $line;
        $this->startColumn = $column;
    }
    /**
     * @param string $append Token content to append
     * @return void
     */
    public function appendToToken(string $append)
    {
        if ($this->value === null) {
            $this->value = "";
        }
        $this->value .= $append;
    }
    /**
     * @param string $append Token content to append
     * @return TokenInterface
     */
    public function endMultilineToken(string $append = '') : \RectorPrefix20210519\Helmich\TypoScriptParser\Tokenizer\TokenInterface
    {
        $value = ($this->value ?? "") . $append;
        $type = $this->type;
        $startLine = $this->startLine;
        $startColumn = $this->startColumn;
        if ($type === null || $startLine === null || $startColumn === null) {
            throw new \RectorPrefix20210519\Helmich\TypoScriptParser\Tokenizer\TokenizerException('cannot call "endMultilineToken" before calling "startMultilineToken"');
        }
        $token = new \RectorPrefix20210519\Helmich\TypoScriptParser\Tokenizer\Token($type, \rtrim($value), $startLine, $startColumn);
        $this->reset();
        return $token;
    }
    /**
     * @return string|null Token type (one of `TokenInterface::TYPE_*`)
     */
    public function currentTokenType()
    {
        return $this->type;
    }
    /**
     * @return void
     */
    private function reset()
    {
        $this->type = null;
        $this->value = null;
        $this->startLine = null;
        $this->startColumn = null;
    }
}