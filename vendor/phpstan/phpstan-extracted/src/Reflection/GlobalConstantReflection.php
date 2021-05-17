<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
interface GlobalConstantReflection
{
    public function getName() : string;
    public function getValueType() : \PHPStan\Type\Type;
    public function isDeprecated() : \PHPStan\TrinaryLogic;
    /**
     * @return string|null
     */
    public function getDeprecatedDescription();
    public function isInternal() : \PHPStan\TrinaryLogic;
    /**
     * @return string|null
     */
    public function getFileName();
}
