<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\Type;
/** @api */
final class TemplateBenevolentUnionType extends \PHPStan\Type\BenevolentUnionType implements \PHPStan\Type\Generic\TemplateType
{
    /** @use TemplateTypeTrait<BenevolentUnionType> */
    use TemplateTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\BenevolentUnionType $bound)
    {
        parent::__construct($bound->getTypes());
        $this->scope = $scope;
        $this->strategy = $templateTypeStrategy;
        $this->variance = $templateTypeVariance;
        $this->name = $name;
        $this->bound = $bound;
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $newBound = $cb($this->getBound());
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\BenevolentUnionType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
}
