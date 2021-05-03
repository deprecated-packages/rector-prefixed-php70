<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Reflection\ReflectionTypeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\ParamTypeFromStrictTypedPropertyRectorTest
 */
final class ParamTypeFromStrictTypedPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ReflectionTypeResolver
     */
    private $reflectionTypeResolver;
    public function __construct(\Rector\TypeDeclaration\Reflection\ReflectionTypeResolver $reflectionTypeResolver)
    {
        $this->reflectionTypeResolver = $reflectionTypeResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add param type from $param set to typed property', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age;

    public function setAge(int $age)
    {
        $this->age = $age;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Param::class];
    }
    /**
     * @param Param $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        return $this->decorateParamWithType($parent, $node);
    }
    /**
     * @return \PhpParser\Node\Param|null
     */
    public function decorateParamWithType(\PhpParser\Node\FunctionLike $functionLike, \PhpParser\Node\Param $param)
    {
        if ($param->type !== null) {
            return null;
        }
        $scope = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $paramName = $this->getName($param);
        $originalParamType = $scope->getVariableType($paramName);
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->getStmts(), \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            if (!$this->nodeComparator->areNodesEqual($assign->expr, $param->var)) {
                continue;
            }
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            if ($this->hasTypeChangedBeforeAssign($assign, $paramName, $originalParamType)) {
                return null;
            }
            $singlePropertyTypeNode = $this->matchPropertySingleTypeNode($assign->var);
            if (!$singlePropertyTypeNode instanceof \PhpParser\Node) {
                return null;
            }
            $param->type = $singlePropertyTypeNode;
            return $param;
        }
        return null;
    }
    /**
     * @return \PhpParser\Node|null
     */
    private function matchPropertySingleTypeNode(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $property = $this->nodeRepository->findPropertyByPropertyFetch($propertyFetch);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            // code from /vendor
            $propertyFetchType = $this->reflectionTypeResolver->resolvePropertyFetchType($propertyFetch);
            if (!$propertyFetchType instanceof \PHPStan\Type\Type) {
                return null;
            }
            return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyFetchType);
        }
        if ($property->type === null) {
            return null;
        }
        // move type to param if not union type
        if ($property->type instanceof \PhpParser\Node\UnionType) {
            return null;
        }
        if ($property->type instanceof \PhpParser\Node\NullableType) {
            return null;
        }
        // needed to avoid reprinting original tokens bug
        $typeNode = clone $property->type;
        $typeNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $typeNode;
    }
    private function hasTypeChangedBeforeAssign(\PhpParser\Node\Expr\Assign $assign, string $paramName, \PHPStan\Type\Type $originalType) : bool
    {
        $scope = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $currentParamType = $scope->getVariableType($paramName);
        return !$currentParamType->equals($originalType);
    }
}
