<?php

declare (strict_types=1);
namespace PHPStan\Rules\Arrays;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
/**
 * @implements Rule<Assign>
 */
class ArrayDestructuringRule implements \PHPStan\Rules\Rule
{
    /** @var RuleLevelHelper */
    private $ruleLevelHelper;
    /** @var NonexistentOffsetInArrayDimFetchCheck */
    private $nonexistentOffsetInArrayDimFetchCheck;
    public function __construct(\PHPStan\Rules\RuleLevelHelper $ruleLevelHelper, \PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchCheck $nonexistentOffsetInArrayDimFetchCheck)
    {
        $this->ruleLevelHelper = $ruleLevelHelper;
        $this->nonexistentOffsetInArrayDimFetchCheck = $nonexistentOffsetInArrayDimFetchCheck;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Expr\Assign::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node->var instanceof \PhpParser\Node\Expr\List_ && !$node->var instanceof \PhpParser\Node\Expr\Array_) {
            return [];
        }
        return $this->getErrors($scope, $node->var, $node->expr);
    }
    /**
     * @param Node\Expr\List_|Node\Expr\Array_ $var
     * @return RuleError[]
     */
    private function getErrors(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $var, \PhpParser\Node\Expr $expr) : array
    {
        $exprTypeResult = $this->ruleLevelHelper->findTypeToCheck($scope, $expr, '', static function (\PHPStan\Type\Type $varType) : bool {
            return $varType->isArray()->yes();
        });
        $exprType = $exprTypeResult->getType();
        if ($exprType instanceof \PHPStan\Type\ErrorType) {
            return [];
        }
        if (!$exprType->isArray()->yes()) {
            return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Cannot use array destructuring on %s.', $exprType->describe(\PHPStan\Type\VerbosityLevel::typeOnly())))->build()];
        }
        $errors = [];
        $i = 0;
        foreach ($var->items as $item) {
            if ($item === null) {
                $i++;
                continue;
            }
            $keyExpr = null;
            if ($item->key === null) {
                $keyType = new \PHPStan\Type\Constant\ConstantIntegerType($i);
                $keyExpr = new \PhpParser\Node\Scalar\LNumber($i);
            } else {
                $keyType = $scope->getType($item->key);
                if ($keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                    $keyExpr = new \PhpParser\Node\Scalar\LNumber($keyType->getValue());
                } elseif ($keyType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                    $keyExpr = new \PhpParser\Node\Scalar\String_($keyType->getValue());
                }
            }
            $itemErrors = $this->nonexistentOffsetInArrayDimFetchCheck->check($scope, $expr, '', $keyType);
            $errors = \array_merge($errors, $itemErrors);
            if ($keyExpr === null) {
                $i++;
                continue;
            }
            if (!$item->value instanceof \PhpParser\Node\Expr\List_ && !$item->value instanceof \PhpParser\Node\Expr\Array_) {
                $i++;
                continue;
            }
            $errors = \array_merge($errors, $this->getErrors($scope, $item->value, new \PhpParser\Node\Expr\ArrayDimFetch($expr, $keyExpr)));
        }
        return $errors;
    }
}
