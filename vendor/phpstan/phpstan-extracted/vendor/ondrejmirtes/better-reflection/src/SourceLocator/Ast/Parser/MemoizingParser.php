<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Ast\Parser;

use PhpParser\ErrorHandler;
use PhpParser\Node;
use PhpParser\Parser;
use function array_key_exists;
use function hash;
use function strlen;
/**
 * @internal
 */
final class MemoizingParser implements \PhpParser\Parser
{
    /** @var Node\Stmt[][]|null[] indexed by source hash */
    private $sourceHashToAst = [];
    /** @var Parser */
    private $wrappedParser;
    public function __construct(\PhpParser\Parser $wrappedParser)
    {
        $this->wrappedParser = $wrappedParser;
    }
    /**
     * {@inheritDoc}
     * @param \PhpParser\ErrorHandler|null $errorHandler
     * @return mixed[]|null
     */
    public function parse(string $code, $errorHandler = null)
    {
        // note: this code is mathematically buggy by default, as we are using a hash to identify
        //       cache entries. The string length is added to further reduce likeliness (although
        //       already imperceptible) of key collisions.
        //       In the "real world", this code will work just fine.
        $hash = \hash('sha256', $code) . ':' . \strlen($code);
        if (\array_key_exists($hash, $this->sourceHashToAst)) {
            return $this->sourceHashToAst[$hash];
        }
        return $this->sourceHashToAst[$hash] = $this->wrappedParser->parse($code, $errorHandler);
    }
}
