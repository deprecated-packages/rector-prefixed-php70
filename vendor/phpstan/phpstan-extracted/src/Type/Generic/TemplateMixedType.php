<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
final class TemplateMixedType extends \PHPStan\Type\MixedType implements \PHPStan\Type\Generic\TemplateType
{
    /** @use TemplateTypeTrait<MixedType> */
    use TemplateTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\MixedType $bound)
    {
        parent::__construct(\true);
        $this->scope = $scope;
        $this->strategy = $templateTypeStrategy;
        $this->variance = $templateTypeVariance;
        $this->name = $name;
        $this->bound = $bound;
    }
    public function isSuperTypeOfMixed(\PHPStan\Type\MixedType $type) : \PHPStan\TrinaryLogic
    {
        return $this->isSuperTypeOf($type);
    }
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        $isSuperType = $this->isSuperTypeOf($acceptingType);
        if ($isSuperType->no()) {
            return $isSuperType;
        }
        return \PHPStan\TrinaryLogic::createYes();
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $newBound = $cb($this->getBound());
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\MixedType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
}
