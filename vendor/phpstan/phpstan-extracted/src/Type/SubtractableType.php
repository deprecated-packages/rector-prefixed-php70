<?php

declare (strict_types=1);
namespace PHPStan\Type;

interface SubtractableType extends \PHPStan\Type\Type
{
    public function subtract(\PHPStan\Type\Type $type) : \PHPStan\Type\Type;
    public function getTypeWithoutSubtractedType() : \PHPStan\Type\Type;
    /**
     * @param \PHPStan\Type\Type|null $subtractedType
     */
    public function changeSubtractedType($subtractedType) : \PHPStan\Type\Type;
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getSubtractedType();
}
