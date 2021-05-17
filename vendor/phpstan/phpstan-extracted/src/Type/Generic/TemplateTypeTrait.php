<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
/**
 * @template TBound of Type
 */
trait TemplateTypeTrait
{
    /** @var string */
    private $name;
    /** @var TemplateTypeScope */
    private $scope;
    /** @var TemplateTypeStrategy */
    private $strategy;
    /** @var TemplateTypeVariance */
    private $variance;
    /** @var TBound */
    private $bound;
    public function getName() : string
    {
        return $this->name;
    }
    public function getScope() : \PHPStan\Type\Generic\TemplateTypeScope
    {
        return $this->scope;
    }
    /** @return TBound */
    public function getBound() : \PHPStan\Type\Type
    {
        return $this->bound;
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        $basicDescription = function () use($level) : string {
            if ($this->bound instanceof \PHPStan\Type\MixedType) {
                // @phpstan-ignore-line
                $boundDescription = '';
            } else {
                // @phpstan-ignore-line
                $boundDescription = \sprintf(' of %s', $this->bound->describe($level));
            }
            return \sprintf('%s%s', $this->name, $boundDescription);
        };
        return $level->handle($basicDescription, $basicDescription, function () use($basicDescription) : string {
            return \sprintf('%s (%s, %s)', $basicDescription(), $this->scope->describe(), $this->isArgument() ? 'argument' : 'parameter');
        });
    }
    public function isArgument() : bool
    {
        return $this->strategy->isArgument();
    }
    public function toArgument() : \PHPStan\Type\Generic\TemplateType
    {
        return new self($this->scope, new \PHPStan\Type\Generic\TemplateTypeArgumentStrategy(), $this->variance, $this->name, \PHPStan\Type\Generic\TemplateTypeHelper::toArgument($this->getBound()));
    }
    public function isValidVariance(\PHPStan\Type\Type $a, \PHPStan\Type\Type $b) : \PHPStan\TrinaryLogic
    {
        return $this->variance->isValidVariance($a, $b);
    }
    public function subtract(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return $this;
    }
    public function getTypeWithoutSubtractedType() : \PHPStan\Type\Type
    {
        return $this;
    }
    /**
     * @param \PHPStan\Type\Type|null $subtractedType
     */
    public function changeSubtractedType($subtractedType) : \PHPStan\Type\Type
    {
        return $this;
    }
    public function equals(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof self && $type->scope->equals($this->scope) && $type->name === $this->name && $this->bound->equals($type->bound);
    }
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        return $this->isSubTypeOf($acceptingType);
    }
    public function accepts(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        return $this->strategy->accepts($this, $type, $strictTypes);
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOf($this);
        }
        return $this->getBound()->isSuperTypeOf($type)->and(\PHPStan\TrinaryLogic::createMaybe());
    }
    public function isSubTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        /** @var Type $bound */
        $bound = $this->getBound();
        if (!$type instanceof $bound && !$this instanceof $type && !$type instanceof \PHPStan\Type\Generic\TemplateType && ($type instanceof \PHPStan\Type\UnionType || $type instanceof \PHPStan\Type\IntersectionType)) {
            return $type->isSuperTypeOf($this);
        }
        if (!$type instanceof \PHPStan\Type\Generic\TemplateType) {
            return $type->isSuperTypeOf($this->getBound());
        }
        if ($this->equals($type)) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        if ($type->getBound()->isSuperTypeOf($this->getBound())->no() && $this->getBound()->isSuperTypeOf($type->getBound())->no()) {
            return \PHPStan\TrinaryLogic::createNo();
        }
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    public function inferTemplateTypes(\PHPStan\Type\Type $receivedType) : \PHPStan\Type\Generic\TemplateTypeMap
    {
        if ($receivedType instanceof \PHPStan\Type\UnionType || $receivedType instanceof \PHPStan\Type\IntersectionType) {
            return $receivedType->inferTemplateTypesOn($this);
        }
        if ($receivedType instanceof \PHPStan\Type\Generic\TemplateType && $this->getBound()->isSuperTypeOf($receivedType->getBound())->yes()) {
            return new \PHPStan\Type\Generic\TemplateTypeMap([$this->name => $receivedType]);
        }
        $map = $this->getBound()->inferTemplateTypes($receivedType);
        $resolvedBound = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($this->getBound(), $map);
        if ($resolvedBound->isSuperTypeOf($receivedType)->yes()) {
            return (new \PHPStan\Type\Generic\TemplateTypeMap([$this->name => $this->shouldGeneralizeInferredType() ? \PHPStan\Type\Generic\TemplateTypeHelper::generalizeType($receivedType) : $receivedType]))->union($map);
        }
        return $map;
    }
    public function getReferencedTemplateTypes(\PHPStan\Type\Generic\TemplateTypeVariance $positionVariance) : array
    {
        return [new \PHPStan\Type\Generic\TemplateTypeReference($this, $positionVariance)];
    }
    public function getVariance() : \PHPStan\Type\Generic\TemplateTypeVariance
    {
        return $this->variance;
    }
    protected function shouldGeneralizeInferredType() : bool
    {
        return \true;
    }
    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['scope'], $properties['strategy'], $properties['variance'], $properties['name'], $properties['bound']);
    }
}
