<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix20210620\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PhpParser\Parser;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20210620\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class AnonymousFunctionFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20210620\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \PhpParser\Parser $parser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parser = $parser;
    }
    /**
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param Identifier|Name|NullableType|UnionType|null $returnTypeNode
     */
    public function create(array $params, array $stmts, $returnTypeNode) : \PhpParser\Node\Expr\Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);
        $anonymousFunctionNode = new \PhpParser\Node\Expr\Closure();
        $anonymousFunctionNode->params = $params;
        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new \PhpParser\Node\Expr\ClosureUse($useVariable);
        }
        if ($returnTypeNode instanceof \PhpParser\Node) {
            $anonymousFunctionNode->returnType = $returnTypeNode;
        }
        $anonymousFunctionNode->stmts = $stmts;
        return $anonymousFunctionNode;
    }
    /**
     * @param Variable|PropertyFetch $expr
     */
    public function createFromPhpMethodReflection(\PHPStan\Reflection\Php\PhpMethodReflection $phpMethodReflection, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\Closure
    {
        /** @var FunctionVariantWithPhpDocs $functionVariantWithPhpDoc */
        $functionVariantWithPhpDoc = $phpMethodReflection->getVariants()[0];
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $newParams = $this->createParams($functionVariantWithPhpDoc->getParameters());
        $anonymousFunction->params = $newParams;
        $innerMethodCall = new \PhpParser\Node\Expr\MethodCall($expr, $phpMethodReflection->getName());
        $innerMethodCall->args = $this->nodeFactory->createArgsFromParams($newParams);
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\MixedType) {
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($functionVariantWithPhpDoc->getReturnType());
            $anonymousFunction->returnType = $returnType;
        }
        // does method return something?
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\VoidType) {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Expression($innerMethodCall);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable && !$this->nodeNameResolver->isName($expr, 'this')) {
            $anonymousFunction->uses[] = new \PhpParser\Node\Expr\ClosureUse($expr);
        }
        return $anonymousFunction;
    }
    /**
     * @return \PhpParser\Node\Expr\Closure|null
     */
    public function createAnonymousFunctionFromString(\PhpParser\Node\Expr $expr)
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            // not supported yet
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = (array) $this->parser->parse($phpCode);
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $firstNode = $contentNodes[0] ?? null;
        if (!$firstNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) : Node {
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return $node;
            }
            $match = \RectorPrefix20210620\Nette\Utils\Strings::match($node->value, self::DIM_FETCH_REGEX);
            if (!$match) {
                return $node;
            }
            $matchesVariable = new \PhpParser\Node\Expr\Variable('matches');
            return new \PhpParser\Node\Expr\ArrayDimFetch($matchesVariable, new \PhpParser\Node\Scalar\LNumber((int) $match['number']));
        });
        $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($stmt);
        $anonymousFunction->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('matches'));
        return $anonymousFunction;
    }
    /**
     * @param Node[] $nodes
     * @param Param[] $paramNodes
     * @return Variable[]
     */
    private function createUseVariablesFromParams(array $nodes, array $paramNodes) : array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->nodeNameResolver->getName($paramNode);
        }
        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Expr\Variable::class);
        /** @var Variable[] $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->nodeNameResolver->isName($variableNode, 'this')) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variableNode);
            if ($variableName === null) {
                continue;
            }
            if (\in_array($variableName, $paramNames, \true)) {
                continue;
            }
            $parentNode = $variableNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentNode instanceof \PhpParser\Node\Expr\Assign) {
                $alreadyAssignedVariables[] = $variableName;
            }
            if ($this->nodeNameResolver->isNames($variableNode, $alreadyAssignedVariables)) {
                continue;
            }
            $filteredVariables[$variableName] = $variableNode;
        }
        return $filteredVariables;
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(array $parameterReflections) : array
    {
        $params = [];
        foreach ($parameterReflections as $parameterReflection) {
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($parameterReflection->getName()));
            if (!$parameterReflection->getType() instanceof \PHPStan\Type\MixedType) {
                $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType());
                $param->type = $paramType;
            }
            $params[] = $param;
        }
        return $params;
    }
}
