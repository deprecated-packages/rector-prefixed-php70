<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
class ClosureCallUnresolvedMethodPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
{
    /** @var UnresolvedMethodPrototypeReflection */
    private $prototype;
    /** @var ClosureType */
    private $closure;
    public function __construct(\PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection $prototype, \PHPStan\Type\ClosureType $closure)
    {
        $this->prototype = $prototype;
        $this->closure = $closure;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        return new self($this->prototype->doNotResolveTemplateTypeMapToBounds(), $this->closure);
    }
    public function getNakedMethod() : \PHPStan\Reflection\MethodReflection
    {
        return $this->getTransformedMethod();
    }
    public function getTransformedMethod() : \PHPStan\Reflection\MethodReflection
    {
        return new \PHPStan\Reflection\Php\ClosureCallMethodReflection($this->prototype->getTransformedMethod(), $this->closure);
    }
    public function withCalledOnType(\PHPStan\Type\Type $type) : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        return new self($this->prototype->withCalledOnType($type), $this->closure);
    }
}
