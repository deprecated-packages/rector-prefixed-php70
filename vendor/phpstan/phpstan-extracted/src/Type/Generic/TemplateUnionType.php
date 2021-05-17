<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class TemplateUnionType extends \PHPStan\Type\UnionType implements \PHPStan\Type\Generic\TemplateType
{
    /** @use TemplateTypeTrait<UnionType> */
    use TemplateTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\UnionType $bound)
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
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\UnionType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
}
