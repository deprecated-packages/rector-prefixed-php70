<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\Type;
/** @api */
interface TemplateType extends \PHPStan\Type\CompoundType
{
    public function getName() : string;
    public function getScope() : \PHPStan\Type\Generic\TemplateTypeScope;
    public function getBound() : \PHPStan\Type\Type;
    public function toArgument() : \PHPStan\Type\Generic\TemplateType;
    public function isArgument() : bool;
    public function isValidVariance(\PHPStan\Type\Type $a, \PHPStan\Type\Type $b) : \PHPStan\TrinaryLogic;
    public function getVariance() : \PHPStan\Type\Generic\TemplateTypeVariance;
}
