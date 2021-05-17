<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Native;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
class NativeFunctionReflection implements \PHPStan\Reflection\FunctionReflection
{
    /** @var string */
    private $name;
    /** @var \PHPStan\Reflection\ParametersAcceptor[] */
    private $variants;
    /** @var \PHPStan\Type\Type|null */
    private $throwType;
    /** @var TrinaryLogic */
    private $hasSideEffects;
    /**
     * @param string $name
     * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
     * @param \PHPStan\Type\Type|null $throwType
     * @param \PHPStan\TrinaryLogic $hasSideEffects
     */
    public function __construct(string $name, array $variants, $throwType, \PHPStan\TrinaryLogic $hasSideEffects)
    {
        $this->name = $name;
        $this->variants = $variants;
        $this->throwType = $throwType;
        $this->hasSideEffects = $hasSideEffects;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getVariants() : array
    {
        return $this->variants;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        return $this->throwType;
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return null;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        if ($this->isVoid()) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        return $this->hasSideEffects;
    }
    private function isVoid() : bool
    {
        foreach ($this->variants as $variant) {
            if (!$variant->getReturnType() instanceof \PHPStan\Type\VoidType) {
                return \false;
            }
        }
        return \true;
    }
    public function isBuiltin() : bool
    {
        return \true;
    }
}
