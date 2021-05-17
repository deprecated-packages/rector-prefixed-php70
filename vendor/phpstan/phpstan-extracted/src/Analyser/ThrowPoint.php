<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
class ThrowPoint
{
    /** @var MutatingScope */
    private $scope;
    /** @var Type */
    private $type;
    /** @var Node\Expr|Node\Stmt */
    private $node;
    /** @var bool */
    private $explicit;
    /** @var bool */
    private $canContainAnyThrowable;
    /**
     * @param MutatingScope $scope
     * @param Type $type
     * @param Node\Expr|Node\Stmt $node
     * @param bool $explicit
     * @param bool $canContainAnyThrowable
     */
    private function __construct(\PHPStan\Analyser\MutatingScope $scope, \PHPStan\Type\Type $type, \PhpParser\Node $node, bool $explicit, bool $canContainAnyThrowable)
    {
        $this->scope = $scope;
        $this->type = $type;
        $this->node = $node;
        $this->explicit = $explicit;
        $this->canContainAnyThrowable = $canContainAnyThrowable;
    }
    /**
     * @param MutatingScope $scope
     * @param Type $type
     * @param Node\Expr|Node\Stmt $node
     * @param bool $canContainAnyThrowable
     * @return self
     */
    public static function createExplicit(\PHPStan\Analyser\MutatingScope $scope, \PHPStan\Type\Type $type, \PhpParser\Node $node, bool $canContainAnyThrowable)
    {
        return new self($scope, $type, $node, \true, $canContainAnyThrowable);
    }
    /**
     * @param MutatingScope $scope
     * @param Node\Expr|Node\Stmt $node
     * @return self
     */
    public static function createImplicit(\PHPStan\Analyser\MutatingScope $scope, \PhpParser\Node $node)
    {
        return new self($scope, new \PHPStan\Type\ObjectType(\Throwable::class), $node, \false, \true);
    }
    public function getScope() : \PHPStan\Analyser\MutatingScope
    {
        return $this->scope;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    /**
     * @return Node\Expr|Node\Stmt
     */
    public function getNode()
    {
        return $this->node;
    }
    public function isExplicit() : bool
    {
        return $this->explicit;
    }
    public function canContainAnyThrowable() : bool
    {
        return $this->canContainAnyThrowable;
    }
    /**
     * @return $this
     */
    public function subtractCatchType(\PHPStan\Type\Type $catchType)
    {
        return new self($this->scope, \PHPStan\Type\TypeCombinator::remove($this->type, $catchType), $this->node, $this->explicit, $this->canContainAnyThrowable);
    }
}
