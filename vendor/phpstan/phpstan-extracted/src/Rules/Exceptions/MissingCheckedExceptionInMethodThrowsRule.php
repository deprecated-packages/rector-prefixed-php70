<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<MethodReturnStatementsNode>
 */
class MissingCheckedExceptionInMethodThrowsRule implements \PHPStan\Rules\Rule
{
    /** @var MissingCheckedExceptionInThrowsCheck */
    private $check;
    public function __construct(\PHPStan\Rules\Exceptions\MissingCheckedExceptionInThrowsCheck $check)
    {
        $this->check = $check;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\MethodReturnStatementsNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $statementResult = $node->getStatementResult();
        $methodReflection = $scope->getFunction();
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $errors = [];
        foreach ($this->check->check($methodReflection->getThrowType(), $statementResult->getThrowPoints()) as list($className, $throwPointNode, $newCatchPosition)) {
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Method %s::%s() throws checked exception %s but it\'s missing from the PHPDoc @throws tag.', $methodReflection->getDeclaringClass()->getDisplayName(), $methodReflection->getName(), $className))->line($throwPointNode->getLine())->identifier('exceptions.missingThrowsTag')->metadata(['exceptionName' => $className, 'newCatchPosition' => $newCatchPosition, 'statementDepth' => $throwPointNode->getAttribute('statementDepth'), 'statementOrder' => $throwPointNode->getAttribute('statementOrder')])->build();
        }
        return $errors;
    }
}
