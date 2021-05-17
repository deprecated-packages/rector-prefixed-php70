<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
class IntersectionTypeUnresolvedMethodPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
{
    /** @var string */
    private $methodName;
    /** @var UnresolvedMethodPrototypeReflection[] */
    private $methodPrototypes;
    /** @var MethodReflection|null */
    private $transformedMethod = null;
    /** @var self|null */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    /**
     * @param UnresolvedMethodPrototypeReflection[] $methodPrototypes
     */
    public function __construct(string $methodName, array $methodPrototypes)
    {
        $this->methodName = $methodName;
        $this->methodPrototypes = $methodPrototypes;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->methodName, \array_map(static function (\PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection $prototype) : UnresolvedMethodPrototypeReflection {
            return $prototype->doNotResolveTemplateTypeMapToBounds();
        }, $this->methodPrototypes));
    }
    public function getNakedMethod() : \PHPStan\Reflection\MethodReflection
    {
        return $this->getTransformedMethod();
    }
    public function getTransformedMethod() : \PHPStan\Reflection\MethodReflection
    {
        if ($this->transformedMethod !== null) {
            return $this->transformedMethod;
        }
        $methods = \array_map(static function (\PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection $prototype) : MethodReflection {
            return $prototype->getTransformedMethod();
        }, $this->methodPrototypes);
        return $this->transformedMethod = new \PHPStan\Reflection\Type\IntersectionTypeMethodReflection($this->methodName, $methods);
    }
    public function withCalledOnType(\PHPStan\Type\Type $type) : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        return new self($this->methodName, \array_map(static function (\PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection $prototype) use($type) : UnresolvedMethodPrototypeReflection {
            return $prototype->withCalledOnType($type);
        }, $this->methodPrototypes));
    }
}
