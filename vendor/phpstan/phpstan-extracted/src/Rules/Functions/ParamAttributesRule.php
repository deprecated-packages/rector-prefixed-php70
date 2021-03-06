<?php

declare (strict_types=1);
namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\AttributesCheck;
use PHPStan\Rules\Rule;
/**
 * @implements Rule<Node\Param>
 */
class ParamAttributesRule implements \PHPStan\Rules\Rule
{
    /** @var AttributesCheck */
    private $attributesCheck;
    public function __construct(\PHPStan\Rules\AttributesCheck $attributesCheck)
    {
        $this->attributesCheck = $attributesCheck;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Param::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $targetName = 'parameter';
        if ($node->flags !== 0) {
            $targetName = 'parameter or property';
            $propertyTargetErrors = $this->attributesCheck->check($scope, $node->attrGroups, \Attribute::TARGET_PROPERTY, $targetName);
            if (\count($propertyTargetErrors) === 0) {
                return $propertyTargetErrors;
            }
        }
        return $this->attributesCheck->check($scope, $node->attrGroups, \Attribute::TARGET_PARAMETER, $targetName);
    }
}
