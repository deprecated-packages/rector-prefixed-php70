<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
final class DocBlockNameImporter
{
    /**
     * @var NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    /**
     * @var ImportingPhpDocNodeTraverserFactory
     */
    private $importingPhpDocNodeTraverserFactory;
    public function __construct(\Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor, \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory $importingPhpDocNodeTraverserFactory)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
        $this->importingPhpDocNodeTraverserFactory = $importingPhpDocNodeTraverserFactory;
    }
    /**
     * @return void
     */
    public function importNames(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \PhpParser\Node $node)
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeTraverser = $this->importingPhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
