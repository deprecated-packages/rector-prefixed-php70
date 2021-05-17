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
class TooWideFunctionThrowTypeRule implements \PHPStan\Rules\Rule
{
    /** @var TooWideThrowTypeCheck */
    private $check;
    public function __construct(\PHPStan\Rules\Exceptions\TooWideThrowTypeCheck $check)
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
        $throwType = $functionReflection->getThrowType();
        if ($throwType === null) {
            return [];
        }
        $errors = [];
        foreach ($this->check->check($throwType, $statementResult->getThrowPoints()) as $throwClass) {
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Function %s() has %s in PHPDoc @throws tag but it\'s not thrown.', $functionReflection->getName(), $throwClass))->identifier('exceptions.tooWideThrowType')->metadata(['exceptionName' => $throwClass, 'statementDepth' => $node->getAttribute('statementDepth'), 'statementOrder' => $node->getAttribute('statementOrder')])->build();
        }
        return $errors;
    }
}
