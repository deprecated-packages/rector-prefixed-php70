<?php

declare (strict_types=1);
namespace Rector\Php52\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php52\Rector\Property\VarToPublicPropertyRector\VarToPublicPropertyRectorTest
 */
final class VarToPublicPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change property modifier from `var` to `public`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    var $name = 'Tom';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public $name = 'Tom';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        // explicitly public
        if ($node->flags !== 0) {
            return null;
        }
        $this->visibilityManipulator->makePublic($node);
        return $node;
    }
}
