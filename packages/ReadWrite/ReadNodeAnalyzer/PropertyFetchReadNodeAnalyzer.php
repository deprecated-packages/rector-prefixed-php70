<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;
final class PropertyFetchReadNodeAnalyzer implements \Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface
{
    /**
     * @var \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer
     */
    private $justReadExprAnalyzer;
    /**
     * @var \Rector\ReadWrite\NodeFinder\NodeUsageFinder
     */
    private $nodeUsageFinder;
    public function __construct(\Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer $justReadExprAnalyzer, \Rector\ReadWrite\NodeFinder\NodeUsageFinder $nodeUsageFinder)
    {
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
        $this->nodeUsageFinder = $nodeUsageFinder;
    }
    public function supports(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\PropertyFetch;
    }
    /**
     * @param PropertyFetch $node
     */
    public function isRead(\PhpParser\Node $node) : bool
    {
        $propertyFetchUsages = $this->nodeUsageFinder->findPropertyFetchUsages($node);
        foreach ($propertyFetchUsages as $propertyFetchUsage) {
            if ($this->justReadExprAnalyzer->isReadContext($propertyFetchUsage)) {
                return \true;
            }
        }
        return \false;
    }
}
