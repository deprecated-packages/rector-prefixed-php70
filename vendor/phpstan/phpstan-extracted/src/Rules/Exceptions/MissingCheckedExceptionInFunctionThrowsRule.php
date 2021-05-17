<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionReturnStatementsNode;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<FunctionReturnStatementsNode>
 */
class MissingCheckedExceptionInFunctionThrowsRule implements \PHPStan\Rules\Rule
{
    /** @var MissingCheckedExceptionInThrowsCheck */
    private $check;
    public function __construct(\PHPStan\Rules\Exceptions\MissingCheckedExceptionInThrowsCheck $check)
    {
        $this->check = $check;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\FunctionReturnStatementsNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $statementResult = $node->getStatementResult();
        $functionReflection = $scope->getFunction();
        if (!$functionReflection instanceof \PHPStan\Reflection\FunctionReflection) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $errors = [];
        foreach ($this->check->check($functionReflection->getThrowType(), $statementResult->getThrowPoints()) as list($className, $throwPointNode, $newCatchPosition)) {
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Function %s() throws checked exception %s but it\'s missing from the PHPDoc @throws tag.', $functionReflection->getName(), $className))->line($throwPointNode->getLine())->identifier('exceptions.missingThrowsTag')->metadata(['exceptionName' => $className, 'newCatchPosition' => $newCatchPosition, 'statementDepth' => $throwPointNode->getAttribute('statementDepth'), 'statementOrder' => $throwPointNode->getAttribute('statementOrder')])->build();
        }
        return $errors;
    }
}
