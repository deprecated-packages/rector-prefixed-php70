<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;
interface UnresolvedPropertyPrototypeReflection
{
    /**
     * @return $this
     */
    public function doNotResolveTemplateTypeMapToBounds();
    public function getNakedProperty() : \PHPStan\Reflection\PropertyReflection;
    public function getTransformedProperty() : \PHPStan\Reflection\PropertyReflection;
    /**
     * @return $this
     */
    public function withFechedOnType(\PHPStan\Type\Type $type);
}
