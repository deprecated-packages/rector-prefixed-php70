<?php

declare (strict_types=1);
namespace PHPStan\Parser;

use PhpParser\ErrorHandler;
class PhpParserDecorator implements \PhpParser\Parser
{
    /** @var \PHPStan\Parser\Parser */
    private $wrappedParser;
    public function __construct(\PHPStan\Parser\Parser $wrappedParser)
    {
        $this->wrappedParser = $wrappedParser;
    }
    /**
     * @param string $code
     * @param \PhpParser\ErrorHandler|null $errorHandler
     * @return mixed[]|null
     */
    public function parse(string $code, $errorHandler = null)
    {
        try {
            return $this->wrappedParser->parseString($code);
        } catch (\PHPStan\Parser\ParserErrorsException $e) {
            $message = $e->getMessage();
            if ($e->getParsedFile() !== null) {
                $message .= \sprintf(' in file %s', $e->getParsedFile());
            }
            throw new \PhpParser\Error($message);
        }
    }
}
