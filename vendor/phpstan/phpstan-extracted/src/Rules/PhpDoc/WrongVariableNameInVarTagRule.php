<?php

declare (strict_types=1);
namespace PHPStan\Rules\PhpDoc;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Node\InClassNode;
use PHPStan\Node\InFunctionNode;
use PHPStan\Node\VirtualNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt>
 */
class WrongVariableNameInVarTagRule implements \PHPStan\Rules\Rule
{
    /** @var FileTypeMapper */
    private $fileTypeMapper;
    /** @var bool */
    private $checkWrongVarUsage;
    public function __construct(\PHPStan\Type\FileTypeMapper $fileTypeMapper, bool $checkWrongVarUsage = \false)
    {
        $this->fileTypeMapper = $fileTypeMapper;
        $this->checkWrongVarUsage = $checkWrongVarUsage;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if ($node instanceof \PhpParser\Node\Stmt\Property || $node instanceof \PhpParser\Node\Stmt\PropertyProperty || $node instanceof \PhpParser\Node\Stmt\ClassConst || $node instanceof \PhpParser\Node\Stmt\Const_ || $node instanceof \PHPStan\Node\VirtualNode && !$node instanceof \PHPStan\Node\InFunctionNode && !$node instanceof \PHPStan\Node\InClassMethodNode && !$node instanceof \PHPStan\Node\InClassNode) {
            return [];
        }
        $varTags = [];
        $function = $scope->getFunction();
        foreach ($node->getComments() as $comment) {
            if (!$comment instanceof \PhpParser\Comment\Doc) {
                continue;
            }
            $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $scope->isInClass() ? $scope->getClassReflection()->getName() : null, $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null, $function !== null ? $function->getName() : null, $comment->getText());
            foreach ($resolvedPhpDoc->getVarTags() as $key => $varTag) {
                $varTags[$key] = $varTag;
            }
        }
        if (\count($varTags) === 0) {
            return [];
        }
        if ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
            return $this->processForeach($node->expr, $node->keyVar, $node->valueVar, $varTags);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Static_) {
            return $this->processStatic($node->vars, $varTags);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Expression) {
            return $this->processExpression($scope, $node->expr, $varTags);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Throw_ || $node instanceof \PhpParser\Node\Stmt\Return_) {
            return $this->processStmt($scope, $varTags, $node->expr);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Global_) {
            return $this->processGlobal($scope, $node, $varTags);
        }
        if ($node instanceof \PHPStan\Node\InClassNode || $node instanceof \PHPStan\Node\InClassMethodNode || $node instanceof \PHPStan\Node\InFunctionNode) {
            if ($this->checkWrongVarUsage) {
                $description = 'a function';
                $originalNode = $node->getOriginalNode();
                if ($originalNode instanceof \PhpParser\Node\Stmt\Interface_) {
                    $description = 'an interface';
                } elseif ($originalNode instanceof \PhpParser\Node\Stmt\Class_) {
                    $description = 'a class';
                } elseif ($originalNode instanceof \PhpParser\Node\Stmt\Trait_) {
                    throw new \PHPStan\ShouldNotHappenException();
                } elseif ($originalNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
                    $description = 'a method';
                }
                return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('PHPDoc tag @var above %s has no effect.', $description))->build()];
            }
            return [];
        }
        return $this->processStmt($scope, $varTags, null);
    }
    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PhpParser\Node\Expr $var
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processAssign(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $var, array $varTags) : array
    {
        $errors = [];
        $hasMultipleMessage = \false;
        $assignedVariables = $this->getAssignedVariables($var);
        foreach (\array_keys($varTags) as $key) {
            if (\is_int($key)) {
                if (\count($varTags) !== 1) {
                    if (!$hasMultipleMessage) {
                        $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('Multiple PHPDoc @var tags above single variable assignment are not supported.')->build();
                        $hasMultipleMessage = \true;
                    }
                } elseif (\count($assignedVariables) !== 1) {
                    $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('PHPDoc tag @var above assignment does not specify variable name.')->build();
                }
                continue;
            }
            if (!$scope->hasVariableType($key)->no()) {
                continue;
            }
            if (\in_array($key, $assignedVariables, \true)) {
                continue;
            }
            if (\count($assignedVariables) === 1 && \count($varTags) === 1) {
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not match assigned variable $%s.', $key, $assignedVariables[0]))->build();
            } else {
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not exist.', $key))->build();
            }
        }
        return $errors;
    }
    /**
     * @param Expr $expr
     * @return string[]
     */
    private function getAssignedVariables(\PhpParser\Node\Expr $expr) : array
    {
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            if (\is_string($expr->name)) {
                return [$expr->name];
            }
            return [];
        }
        if ($expr instanceof \PhpParser\Node\Expr\List_ || $expr instanceof \PhpParser\Node\Expr\Array_) {
            $names = [];
            foreach ($expr->items as $item) {
                if ($item === null) {
                    continue;
                }
                $names = \array_merge($names, $this->getAssignedVariables($item->value));
            }
            return $names;
        }
        return [];
    }
    /**
     * @param \PhpParser\Node\Expr|null $keyVar
     * @param \PhpParser\Node\Expr $valueVar
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processForeach(\PhpParser\Node\Expr $iterateeExpr, $keyVar, \PhpParser\Node\Expr $valueVar, array $varTags) : array
    {
        $variableNames = [];
        if ($iterateeExpr instanceof \PhpParser\Node\Expr\Variable && \is_string($iterateeExpr->name)) {
            $variableNames[] = $iterateeExpr->name;
        }
        if ($keyVar instanceof \PhpParser\Node\Expr\Variable && \is_string($keyVar->name)) {
            $variableNames[] = $keyVar->name;
        }
        $variableNames = \array_merge($variableNames, $this->getAssignedVariables($valueVar));
        $errors = [];
        foreach (\array_keys($varTags) as $name) {
            if (\is_int($name)) {
                if (\count($variableNames) === 1) {
                    continue;
                }
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('PHPDoc tag @var above foreach loop does not specify variable name.')->build();
                continue;
            }
            if (\in_array($name, $variableNames, \true)) {
                continue;
            }
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not match any variable in the foreach loop: %s', $name, \implode(', ', \array_map(static function (string $name) : string {
                return \sprintf('$%s', $name);
            }, $variableNames))))->build();
        }
        return $errors;
    }
    /**
     * @param \PhpParser\Node\Stmt\StaticVar[] $vars
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processStatic(array $vars, array $varTags) : array
    {
        $variableNames = [];
        foreach ($vars as $var) {
            if (!\is_string($var->var->name)) {
                continue;
            }
            $variableNames[$var->var->name] = \true;
        }
        $errors = [];
        foreach (\array_keys($varTags) as $name) {
            if (\is_int($name)) {
                if (\count($vars) === 1) {
                    continue;
                }
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('PHPDoc tag @var above multiple static variables does not specify variable name.')->build();
                continue;
            }
            if (isset($variableNames[$name])) {
                continue;
            }
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not match any static variable: %s', $name, \implode(', ', \array_map(static function (string $name) : string {
                return \sprintf('$%s', $name);
            }, \array_keys($variableNames)))))->build();
        }
        return $errors;
    }
    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PhpParser\Node\Expr $expr
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processExpression(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $expr, array $varTags) : array
    {
        if ($expr instanceof \PhpParser\Node\Expr\Assign || $expr instanceof \PhpParser\Node\Expr\AssignRef) {
            return $this->processAssign($scope, $expr->var, $varTags);
        }
        return $this->processStmt($scope, $varTags, null);
    }
    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @param Expr|null $defaultExpr
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processStmt(\PHPStan\Analyser\Scope $scope, array $varTags, $defaultExpr) : array
    {
        $errors = [];
        $variableLessVarTags = [];
        foreach ($varTags as $name => $varTag) {
            if (\is_int($name)) {
                $variableLessVarTags[] = $varTag;
                continue;
            }
            if (!$scope->hasVariableType($name)->no()) {
                continue;
            }
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not exist.', $name))->build();
        }
        if (\count($variableLessVarTags) !== 1 || $defaultExpr === null) {
            if (\count($variableLessVarTags) > 0) {
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('PHPDoc tag @var does not specify variable name.')->build();
            }
        }
        return $errors;
    }
    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
     * @return \PHPStan\Rules\RuleError[]
     */
    private function processGlobal(\PHPStan\Analyser\Scope $scope, \PhpParser\Node\Stmt\Global_ $node, array $varTags) : array
    {
        $variableNames = [];
        foreach ($node->vars as $var) {
            if (!$var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!\is_string($var->name)) {
                continue;
            }
            $variableNames[$var->name] = \true;
        }
        $errors = [];
        foreach (\array_keys($varTags) as $name) {
            if (\is_int($name)) {
                if (\count($variableNames) === 1) {
                    continue;
                }
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message('PHPDoc tag @var above multiple global variables does not specify variable name.')->build();
                continue;
            }
            if (isset($variableNames[$name])) {
                continue;
            }
            $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Variable $%s in PHPDoc tag @var does not match any global variable: %s', $name, \implode(', ', \array_map(static function (string $name) : string {
                return \sprintf('$%s', $name);
            }, \array_keys($variableNames)))))->build();
        }
        return $errors;
    }
}
