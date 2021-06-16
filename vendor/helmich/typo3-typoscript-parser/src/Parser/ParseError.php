<?php

declare (strict_types=1);
namespace RectorPrefix20210616\Helmich\TypoScriptParser\Parser;

use Exception;
class ParseError extends \Exception
{
    /** @var int|null */
    private $sourceLine;
    /**
     * @param int|null $line
     */
    public function __construct(string $message = "", int $code = 0, $line = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sourceLine = $line;
    }
    /**
     * @return int|null
     */
    public function getSourceLine()
    {
        return $this->sourceLine;
    }
}
