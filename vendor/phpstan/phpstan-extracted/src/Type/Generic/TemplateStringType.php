<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\Type\StringType;
use PHPStan\Type\Traits\UndecidedComparisonCompoundTypeTrait;
use PHPStan\Type\Type;
final class TemplateStringType extends \PHPStan\Type\StringType implements \PHPStan\Type\Generic\TemplateType
{
    /** @use TemplateTypeTrait<StringType> */
    use TemplateTypeTrait;
    use UndecidedComparisonCompoundTypeTrait;
    public function __construct(\PHPStan\Type\Generic\TemplateTypeScope $scope, \PHPStan\Type\Generic\TemplateTypeStrategy $templateTypeStrategy, \PHPStan\Type\Generic\TemplateTypeVariance $templateTypeVariance, string $name, \PHPStan\Type\StringType $bound)
    {
        $this->scope = $scope;
        $this->strategy = $templateTypeStrategy;
        $this->variance = $templateTypeVariance;
        $this->name = $name;
        $this->bound = $bound;
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $newBound = $cb($this->getBound());
        if ($this->getBound() !== $newBound && $newBound instanceof \PHPStan\Type\StringType) {
            return new self($this->scope, $this->strategy, $this->variance, $this->name, $newBound);
        }
        return $this;
    }
    protected function shouldGeneralizeInferredType() : bool
    {
        return \false;
    }
}
