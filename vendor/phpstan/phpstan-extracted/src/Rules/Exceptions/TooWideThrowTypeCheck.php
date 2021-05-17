<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PHPStan\Analyser\ThrowPoint;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;
class TooWideThrowTypeCheck
{
    /**
     * @param Type $throwType
     * @param ThrowPoint[] $throwPoints
     * @return string[]
     */
    public function check(\PHPStan\Type\Type $throwType, array $throwPoints) : array
    {
        if ($throwType instanceof \PHPStan\Type\VoidType) {
            return [];
        }
        $throwPointType = \PHPStan\Type\TypeCombinator::union(...\array_map(static function (\PHPStan\Analyser\ThrowPoint $throwPoint) : Type {
            if (!$throwPoint->isExplicit()) {
                return new \PHPStan\Type\NeverType();
            }
            return $throwPoint->getType();
        }, $throwPoints));
        $throwClasses = [];
        foreach (\PHPStan\Type\TypeUtils::flattenTypes($throwType) as $type) {
            if (!$throwPointType instanceof \PHPStan\Type\NeverType && !$type->isSuperTypeOf($throwPointType)->no()) {
                continue;
            }
            $throwClasses[] = $type->describe(\PHPStan\Type\VerbosityLevel::typeOnly());
        }
        return $throwClasses;
    }
}
