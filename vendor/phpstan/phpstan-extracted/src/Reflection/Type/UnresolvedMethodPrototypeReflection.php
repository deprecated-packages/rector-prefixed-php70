<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
interface UnresolvedMethodPrototypeReflection
{
    /**
     * @return $this
     */
    public function doNotResolveTemplateTypeMapToBounds();
    public function getNakedMethod() : \PHPStan\Reflection\MethodReflection;
    public function getTransformedMethod() : \PHPStan\Reflection\MethodReflection;
    /**
     * @return $this
     */
    public function withCalledOnType(\PHPStan\Type\Type $type);
}
