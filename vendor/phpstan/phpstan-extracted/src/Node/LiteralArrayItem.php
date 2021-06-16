<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr\ArrayItem;
use PHPStan\Analyser\Scope;
/** @api */
class LiteralArrayItem
{
    /** @var Scope */
    private $scope;
    /** @var ArrayItem|null */
    private $arrayItem;
    /**
     * @param \PhpParser\Node\Expr\ArrayItem|null $arrayItem
     */
    public function __construct(\PHPStan\Analyser\Scope $scope, $arrayItem)
    {
        $this->scope = $scope;
        $this->arrayItem = $arrayItem;
    }
    public function getScope() : \PHPStan\Analyser\Scope
    {
        return $this->scope;
    }
    /**
     * @return \PhpParser\Node\Expr\ArrayItem|null
     */
    public function getArrayItem()
    {
        return $this->arrayItem;
    }
}
