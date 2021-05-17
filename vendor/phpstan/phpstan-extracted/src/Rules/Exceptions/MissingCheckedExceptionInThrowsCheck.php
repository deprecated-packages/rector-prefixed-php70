<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\ThrowPoint;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
class MissingCheckedExceptionInThrowsCheck
{
    /** @var ExceptionTypeResolver */
    private $exceptionTypeResolver;
    public function __construct(\PHPStan\Rules\Exceptions\ExceptionTypeResolver $exceptionTypeResolver)
    {
        $this->exceptionTypeResolver = $exceptionTypeResolver;
    }
    /**
     * @param Type|null $throwType
     * @param ThrowPoint[] $throwPoints
     * @return array<int, array{string, Node\Expr|Node\Stmt, int|null}>
     */
    public function check($throwType, array $throwPoints) : array
    {
        if ($throwType === null) {
            $throwType = new \PHPStan\Type\NeverType();
        }
        $classes = [];
        foreach ($throwPoints as $throwPoint) {
            if (!$throwPoint->isExplicit()) {
                continue;
            }
            foreach (\PHPStan\Type\TypeUtils::flattenTypes($throwPoint->getType()) as $throwPointType) {
                if ($throwPointType->isSuperTypeOf(new \PHPStan\Type\ObjectType(\Throwable::class))->yes()) {
                    continue;
                }
                if ($throwType->isSuperTypeOf($throwPointType)->yes()) {
                    continue;
                }
                if ($throwPointType instanceof \PHPStan\Type\TypeWithClassName && !$this->exceptionTypeResolver->isCheckedException($throwPointType->getClassName())) {
                    continue;
                }
                $classes[] = [$throwPointType->describe(\PHPStan\Type\VerbosityLevel::typeOnly()), $throwPoint->getNode(), $this->getNewCatchPosition($throwPointType, $throwPoint->getNode())];
            }
        }
        return $classes;
    }
    /**
     * @return int|null
     */
    private function getNewCatchPosition(\PHPStan\Type\Type $throwPointType, \PhpParser\Node $throwPointNode)
    {
        if ($throwPointType instanceof \PHPStan\Type\TypeWithClassName) {
            // to get rid of type subtraction
            $throwPointType = new \PHPStan\Type\ObjectType($throwPointType->getClassName());
        }
        $tryCatch = $this->findTryCatch($throwPointNode);
        if ($tryCatch === null) {
            return null;
        }
        $position = 0;
        foreach ($tryCatch->catches as $catch) {
            $type = \PHPStan\Type\TypeCombinator::union(...\array_map(static function (\PhpParser\Node\Name $class) : ObjectType {
                return new \PHPStan\Type\ObjectType($class->toString());
            }, $catch->types));
            if (!$throwPointType->isSuperTypeOf($type)->yes()) {
                continue;
            }
            $position++;
        }
        return $position;
    }
    /**
     * @return \PhpParser\Node\Stmt\TryCatch|null
     */
    private function findTryCatch(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Stmt\TryCatch) {
            return $node;
        }
        $parent = $node->getAttribute('parent');
        if ($parent === null) {
            return null;
        }
        return $this->findTryCatch($parent);
    }
}
