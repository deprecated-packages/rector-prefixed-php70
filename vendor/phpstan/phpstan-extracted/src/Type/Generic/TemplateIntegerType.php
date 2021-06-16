<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\IntegerType;
use PHPStan\Type\Traits\UndecidedComparisonCompoundTypeTrait;
use PHPStan\Type\Type;
/** @api */
final class TemplateIntegerType extends \PHPStan\Type\IntegerType implements \PHPStan\Type\Generic\TemplateType
{
    /** @use TemplateTypeTrait<IntegerType> */
    use TemplateTypeTrait;
    use UndecidedComparisonCompoundTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\IntegerType $bound)
    {
        parent::__construct();
        $this->scope = $scope;
        $this->strategy = $templateTypeStrategy;
        $this->variance = $templateTypeVariance;
        $this->name = $name;
        $this->bound = $bound;
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $newBound = $cb($this->getBound());
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\IntegerType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
    protected function shouldGeneralizeInferredType() : bool
    {
        return \false;
    }
}
