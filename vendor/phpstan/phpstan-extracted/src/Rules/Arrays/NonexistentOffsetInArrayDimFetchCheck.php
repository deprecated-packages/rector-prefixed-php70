<?php

declare (strict_types=1);
namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
class NonexistentOffsetInArrayDimFetchCheck
{
    /** @var RuleLevelHelper */
    private $ruleLevelHelper;
    /** @var bool */
    private $reportMaybes;
    public function __construct(\PHPStan\Rules\RuleLevelHelper $ruleLevelHelper, bool $reportMaybes)
    {
        $this->ruleLevelHelper = $ruleLevelHelper;
        $this->reportMaybes = $reportMaybes;
    }
    /**
     * @param Scope $scope
     * @param Expr $var
     * @param string $unknownClassPattern
     * @param Type $dimType
     * @return RuleError[]
     */
    public function check(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $var, string $unknownClassPattern, \PHPStan\Type\Type $dimType) : array
    {
        $typeResult = $this->ruleLevelHelper->findTypeToCheck($scope, $var, $unknownClassPattern, static function (\PHPStan\Type\Type $type) use($dimType) : bool {
            return $type->hasOffsetValueType($dimType)->yes();
        });
        $type = $typeResult->getType();
        if ($type instanceof \PHPStan\Type\ErrorType) {
            return $typeResult->getUnknownClassErrors();
        }
        $hasOffsetValueType = $type->hasOffsetValueType($dimType);
        $report = $hasOffsetValueType->no();
        if ($hasOffsetValueType->maybe()) {
            $constantArrays = \PHPStan\Type\TypeUtils::getOldConstantArrays($type);
            if (\count($constantArrays) > 0) {
                foreach ($constantArrays as $constantArray) {
                    if ($constantArray->hasOffsetValueType($dimType)->no()) {
                        $report = \true;
                        break;
                    }
                }
            }
        }
        if (!$report && $this->reportMaybes) {
            foreach (\PHPStan\Type\TypeUtils::flattenTypes($type) as $innerType) {
                if ($dimType instanceof \PHPStan\Type\UnionType) {
                    if ($innerType->hasOffsetValueType($dimType)->no()) {
                        $report = \true;
                        break;
                    }
                    continue;
                }
                foreach (\PHPStan\Type\TypeUtils::flattenTypes($dimType) as $innerDimType) {
                    if ($innerType->hasOffsetValueType($innerDimType)->no()) {
                        $report = \true;
                        break;
                    }
                }
            }
        }
        if ($report) {
            if ($scope->isInExpressionAssign($var)) {
                return [];
            }
            return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Offset %s does not exist on %s.', $dimType->describe(\PHPStan\Type\VerbosityLevel::value()), $type->describe(\PHPStan\Type\VerbosityLevel::value())))->build()];
        }
        return [];
    }
}
