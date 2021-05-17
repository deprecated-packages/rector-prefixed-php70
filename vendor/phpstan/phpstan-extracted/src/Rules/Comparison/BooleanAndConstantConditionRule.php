<?php

declare (strict_types=1);
namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PHPStan\Node\BooleanAndNode;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
/**
 * @implements \PHPStan\Rules\Rule<BooleanAndNode>
 */
class BooleanAndConstantConditionRule implements \PHPStan\Rules\Rule
{
    /** @var ConstantConditionRuleHelper */
    private $helper;
    /** @var bool */
    private $treatPhpDocTypesAsCertain;
    /** @var bool */
    private $checkLogicalAndConstantCondition;
    public function __construct(\PHPStan\Rules\Comparison\ConstantConditionRuleHelper $helper, bool $treatPhpDocTypesAsCertain, bool $checkLogicalAndConstantCondition)
    {
        $this->helper = $helper;
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
        $this->checkLogicalAndConstantCondition = $checkLogicalAndConstantCondition;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\BooleanAndNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $errors = [];
        /** @var BooleanAnd|LogicalAnd $originalNode */
        $originalNode = $node->getOriginalNode();
        if (!$originalNode instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd && !$this->checkLogicalAndConstantCondition) {
            return [];
        }
        $leftType = $this->helper->getBooleanType($scope, $originalNode->left);
        $tipText = 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.';
        if ($leftType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            $addTipLeft = function (\PHPStan\Rules\RuleErrorBuilder $ruleErrorBuilder) use($scope, $tipText, $originalNode) : RuleErrorBuilder {
                if (!$this->treatPhpDocTypesAsCertain) {
                    return $ruleErrorBuilder;
                }
                $booleanNativeType = $this->helper->getNativeBooleanType($scope, $originalNode->left);
                if ($booleanNativeType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                    return $ruleErrorBuilder;
                }
                return $ruleErrorBuilder->tip($tipText);
            };
            $errors[] = $addTipLeft(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Left side of && is always %s.', $leftType->getValue() ? 'true' : 'false')))->line($originalNode->left->getLine())->build();
        }
        $rightScope = $node->getRightScope();
        $rightType = $this->helper->getBooleanType($rightScope, $originalNode->right);
        if ($rightType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            $addTipRight = function (\PHPStan\Rules\RuleErrorBuilder $ruleErrorBuilder) use($rightScope, $originalNode, $tipText) : RuleErrorBuilder {
                if (!$this->treatPhpDocTypesAsCertain) {
                    return $ruleErrorBuilder;
                }
                $booleanNativeType = $this->helper->getNativeBooleanType($rightScope->doNotTreatPhpDocTypesAsCertain(), $originalNode->right);
                if ($booleanNativeType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                    return $ruleErrorBuilder;
                }
                return $ruleErrorBuilder->tip($tipText);
            };
            $errors[] = $addTipRight(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Right side of && is always %s.', $rightType->getValue() ? 'true' : 'false')))->line($originalNode->right->getLine())->build();
        }
        if (\count($errors) === 0) {
            $nodeType = $scope->getType($originalNode);
            if ($nodeType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                $addTip = function (\PHPStan\Rules\RuleErrorBuilder $ruleErrorBuilder) use($scope, $originalNode, $tipText) : RuleErrorBuilder {
                    if (!$this->treatPhpDocTypesAsCertain) {
                        return $ruleErrorBuilder;
                    }
                    $booleanNativeType = $scope->doNotTreatPhpDocTypesAsCertain()->getType($originalNode);
                    if ($booleanNativeType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                        return $ruleErrorBuilder;
                    }
                    return $ruleErrorBuilder->tip($tipText);
                };
                $errors[] = $addTip(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Result of && is always %s.', $nodeType->getValue() ? 'true' : 'false')))->build();
            }
        }
        return $errors;
    }
}
