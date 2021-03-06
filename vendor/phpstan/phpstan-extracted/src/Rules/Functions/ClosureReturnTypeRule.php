<?php

declare (strict_types=1);
namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClosureReturnStatementsNode;
use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Type\TypeCombinator;
/**
 * @implements \PHPStan\Rules\Rule<ClosureReturnStatementsNode>
 */
class ClosureReturnTypeRule implements \PHPStan\Rules\Rule
{
    /** @var \PHPStan\Rules\FunctionReturnTypeCheck */
    private $returnTypeCheck;
    public function __construct(\PHPStan\Rules\FunctionReturnTypeCheck $returnTypeCheck)
    {
        $this->returnTypeCheck = $returnTypeCheck;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\ClosureReturnStatementsNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$scope->isInAnonymousFunction()) {
            return [];
        }
        /** @var \PHPStan\Type\Type $returnType */
        $returnType = $scope->getAnonymousFunctionReturnType();
        $containsNull = \PHPStan\Type\TypeCombinator::containsNull($returnType);
        $hasNativeTypehint = $node->getClosureExpr()->returnType !== null;
        $messages = [];
        foreach ($node->getReturnStatements() as $returnStatement) {
            $returnNode = $returnStatement->getReturnNode();
            $returnExpr = $returnNode->expr;
            if ($returnExpr === null && $containsNull && !$hasNativeTypehint) {
                $returnExpr = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name\FullyQualified('null'));
            }
            $returnMessages = $this->returnTypeCheck->checkReturnType($returnStatement->getScope(), $returnType, $returnExpr, $returnNode, 'Anonymous function should return %s but empty return statement found.', 'Anonymous function with return type void returns %s but should not return anything.', 'Anonymous function should return %s but returns %s.', 'Anonymous function should never return but return statement found.', \count($node->getYieldStatements()) > 0);
            foreach ($returnMessages as $returnMessage) {
                $messages[] = $returnMessage;
            }
        }
        return $messages;
    }
}
