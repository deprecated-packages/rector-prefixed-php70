<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;
/**
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecMethodToPHPUnitMethodRector extends \Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;
    /**
     * @var \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming
     */
    private $phpSpecRenaming;
    public function __construct(\Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator, \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming $phpSpecRenaming)
    {
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->phpSpecRenaming = $phpSpecRenaming;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->isInPhpSpecBehavior($node)) {
            return null;
        }
        if ($this->isName($node, 'letGo')) {
            $node->name = new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::TEAR_DOWN);
            $this->visibilityManipulator->makeProtected($node);
            $this->phpUnitTypeDeclarationDecorator->decorate($node);
        } elseif ($this->isName($node, 'let')) {
            $node->name = new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::SET_UP);
            $this->visibilityManipulator->makeProtected($node);
            $this->phpUnitTypeDeclarationDecorator->decorate($node);
        } else {
            $this->processTestMethod($node);
        }
        return $node;
    }
    /**
     * @return void
     */
    private function processTestMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isName($classMethod, 'getMatchers')) {
            return;
        }
        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($classMethod);
        // reorder instantiation + expected exception
        $previousStmt = null;
        foreach ((array) $classMethod->stmts as $key => $stmt) {
            if ($previousStmt && \strpos($this->print($stmt), 'duringInstantiation') !== \false && \strpos($this->print($previousStmt), 'beConstructedThrough') !== \false) {
                $classMethod->stmts[$key - 1] = $stmt;
                $classMethod->stmts[$key] = $previousStmt;
            }
            $previousStmt = $stmt;
        }
    }
}
