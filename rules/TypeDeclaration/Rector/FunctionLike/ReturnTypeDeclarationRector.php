<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\ChildPopulator\ChildReturnPopulator;
use Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VendorLocker\VendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\ReturnTypeDeclarationRectorTest
 */
final class ReturnTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @var \Rector\TypeDeclaration\ChildPopulator\ChildReturnPopulator
     */
    private $childReturnPopulator;
    /**
     * @var \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    private $returnTypeAlreadyAddedChecker;
    /**
     * @var \Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover
     */
    private $nonInformativeReturnTagRemover;
    /**
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @var \Rector\TypeDeclaration\PhpParserTypeAnalyzer
     */
    private $phpParserTypeAnalyzer;
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator
     */
    private $objectTypeComparator;
    /**
     * @var \Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer
     */
    private $externalFullyQualifiedAnalyzer;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer, \Rector\TypeDeclaration\ChildPopulator\ChildReturnPopulator $childReturnPopulator, \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker, \Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover $nonInformativeReturnTagRemover, \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, \Rector\VendorLocker\VendorLockResolver $vendorLockResolver, \Rector\TypeDeclaration\PhpParserTypeAnalyzer $phpParserTypeAnalyzer, \Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator $objectTypeComparator, \Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer $externalFullyQualifiedAnalyzer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->childReturnPopulator = $childReturnPopulator;
        $this->returnTypeAlreadyAddedChecker = $returnTypeAlreadyAddedChecker;
        $this->nonInformativeReturnTagRemover = $nonInformativeReturnTagRemover;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->phpParserTypeAnalyzer = $phpParserTypeAnalyzer;
        $this->objectTypeComparator = $objectTypeComparator;
        $this->externalFullyQualifiedAnalyzer = $externalFullyQualifiedAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change @return types and type from static analysis to type declarations if not a BC-break', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getCount(): int
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        if ($this->shouldSkipClassLike($node)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod && $this->shouldSkipClassMethod($node)) {
            return null;
        }
        $inferedReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers($node, [\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer::class]);
        if ($inferedReturnType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($this->returnTypeAlreadyAddedChecker->isSameOrBetterReturnTypeAlreadyAdded($node, $inferedReturnType)) {
            return null;
        }
        return $this->processType($node, $inferedReturnType);
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return \PhpParser\Node|null
     */
    private function processType(\PhpParser\Node $node, \PHPStan\Type\Type $inferedType)
    {
        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::KIND_RETURN);
        // nothing to change in PHP code
        if (!$inferredReturnNode instanceof \PhpParser\Node) {
            return null;
        }
        if ($this->shouldSkipInferredReturnNode($node)) {
            return null;
        }
        // should be previous overridden?
        if ($node->returnType !== null && $this->shouldSkipExistingReturnType($node, $inferedType)) {
            return null;
        }
        /** @var Name|NullableType|PhpParserUnionType $inferredReturnNode */
        $this->addReturnType($node, $inferredReturnNode);
        $this->nonInformativeReturnTagRemover->removeReturnTagIfNotUseful($node);
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->childReturnPopulator->populateChildren($node, $inferedType);
        }
        return $node;
    }
    private function shouldSkipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return \true;
        }
        return $this->vendorLockResolver->isReturnChangeVendorLockedIn($classMethod);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkipInferredReturnNode(\PhpParser\Node\FunctionLike $functionLike) : bool
    {
        // already overridden by previous populateChild() method run
        if ($functionLike->returnType === null) {
            return \false;
        }
        return (bool) $functionLike->returnType->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DO_NOT_CHANGE);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkipExistingReturnType(\PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\Type $inferedType) : bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn($functionLike)) {
            return \true;
        }
        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        if ($this->objectTypeComparator->isCurrentObjectTypeSubType($currentType, $inferedType)) {
            return \true;
        }
        return $this->isNullableTypeSubType($currentType, $inferedType);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     * @param Name|NullableType|PhpParserUnionType $inferredReturnNode
     * @return void
     */
    private function addReturnType(\PhpParser\Node\FunctionLike $functionLike, \PhpParser\Node $inferredReturnNode)
    {
        if ($this->isExternalVoid($functionLike, $inferredReturnNode)) {
            return;
        }
        if ($functionLike->returnType === null) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        $isSubtype = $this->phpParserTypeAnalyzer->isCovariantSubtypeOf($inferredReturnNode, $functionLike->returnType);
        if ($this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        if (!$isSubtype) {
            // type override with correct one
            $functionLike->returnType = $inferredReturnNode;
        }
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     * @param Name|NullableType|PhpParserUnionType $inferredReturnNode
     */
    private function isExternalVoid(\PhpParser\Node\FunctionLike $functionLike, \PhpParser\Node $inferredReturnNode) : bool
    {
        $classLike = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $hasExternalClassOrInterfaceOrTrait = $this->externalFullyQualifiedAnalyzer->hasExternalFullyQualifieds($classLike);
        return $functionLike->returnType === null && $hasExternalClassOrInterfaceOrTrait && $this->isName($inferredReturnNode, 'void');
    }
    private function isNullableTypeSubType(\PHPStan\Type\Type $currentType, \PHPStan\Type\Type $inferedType) : bool
    {
        if (!$currentType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        if (!$inferedType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        return $inferedType->isSubTypeOf($currentType)->yes();
    }
    private function shouldSkipClassLike(\PhpParser\Node\FunctionLike $functionLike) : bool
    {
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $classLike = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        return !$classLike instanceof \PhpParser\Node\Stmt\Class_;
    }
}
