<?php

declare (strict_types=1);
namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PHPStan\Node\BooleanOrNode;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
/**
 * @implements \PHPStan\Rules\Rule<BooleanOrNode>
 */
class BooleanOrConstantConditionRule implements \PHPStan\Rules\Rule
{
    /** @var ConstantConditionRuleHelper */
    private $helper;
    /** @var bool */
    private $treatPhpDocTypesAsCertain;
    /** @var bool */
    private $checkLogicalOrConstantCondition;
    public function __construct(\PHPStan\Rules\Comparison\ConstantConditionRuleHelper $helper, bool $treatPhpDocTypesAsCertain, bool $checkLogicalOrConstantCondition)
    {
        $this->helper = $helper;
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
        $this->checkLogicalOrConstantCondition = $checkLogicalOrConstantCondition;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\BooleanOrNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $originalNode = $node->getOriginalNode();
        if (!$originalNode instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr && !$this->checkLogicalOrConstantCondition) {
            return [];
        }
        $messages = [];
        $leftType = $this->helper->getBooleanType($scope, $originalNode->left);
        $tipText = 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.';
        if ($leftType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            $addTipLeft = function (\PHPStan\Rules\RuleErrorBuilder $ruleErrorBuilder) use($scope, $originalNode, $tipText) : RuleErrorBuilder {
                if (!$this->treatPhpDocTypesAsCertain) {
                    return $ruleErrorBuilder;
                }
                $booleanNativeType = $this->helper->getNativeBooleanType($scope, $originalNode->left);
                if ($booleanNativeType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                    return $ruleErrorBuilder;
                }
                return $ruleErrorBuilder->tip($tipText);
            };
            $messages[] = $addTipLeft(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Left side of || is always %s.', $leftType->getValue() ? 'true' : 'false')))->line($originalNode->left->getLine())->build();
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
            $messages[] = $addTipRight(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Right side of || is always %s.', $rightType->getValue() ? 'true' : 'false')))->line($originalNode->right->getLine())->build();
        }
        if (\count($messages) === 0) {
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
                $messages[] = $addTip(\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Result of || is always %s.', $nodeType->getValue() ? 'true' : 'false')))->build();
            }
        }
        return $messages;
    }
}
