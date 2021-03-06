<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors;

use RectorPrefix20210620\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210620\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Rector\Core\Contract\Rector\RectorInterface;
abstract class AbstractVisitor implements \RectorPrefix20210620\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @var bool
     */
    protected $hasChanged = \false;
    /**
     * @return void
     */
    public function enterTree(array $statements)
    {
    }
    /**
     * @return void
     */
    public function enterNode(\RectorPrefix20210620\Helmich\TypoScriptParser\Parser\AST\Statement $statement)
    {
    }
    /**
     * @return void
     */
    public function exitNode(\RectorPrefix20210620\Helmich\TypoScriptParser\Parser\AST\Statement $statement)
    {
    }
    /**
     * @return void
     */
    public function exitTree(array $statements)
    {
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
}
