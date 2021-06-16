<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\Traits\UndecidedComparisonCompoundTypeTrait;
use PHPStan\Type\Type;
/** @api */
final class TemplateGenericObjectType extends \PHPStan\Type\Generic\GenericObjectType implements \PHPStan\Type\Generic\TemplateType
{
    use UndecidedComparisonCompoundTypeTrait;
    /** @use TemplateTypeTrait<GenericObjectType> */
    use TemplateTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\Generic\GenericObjectType $bound)
    {
        parent::__construct($bound->getClassName(), $bound->getTypes());
        $this->scope = $scope;
        $this->strategy = $templateTypeStrategy;
        $this->variance = $templateTypeVariance;
        $this->name = $name;
        $this->bound = $bound;
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $newBound = $cb($this->getBound());
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
    /**
     * @param \PHPStan\Type\Type|null $subtractedType
     */
    protected function recreate(string $className, array $types, $subtractedType) : \PHPStan\Type\Generic\GenericObjectType
    {
        return new self($this->scope, $this->strategy, $this->variance, $this->name, $this->getBound());
    }
}
