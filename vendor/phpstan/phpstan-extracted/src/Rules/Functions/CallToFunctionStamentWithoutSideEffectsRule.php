<?php

declare (strict_types=1);
namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NeverType;
use PHPStan\Type\VoidType;
/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Expression>
 */
class CallToFunctionStamentWithoutSideEffectsRule implements \PHPStan\Rules\Rule
{
    /** @var \PHPStan\Reflection\ReflectionProvider */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt\Expression::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return [];
        }
        $funcCall = $node->expr;
        if (!$funcCall->name instanceof \PhpParser\Node\Name) {
            return [];
        }
        if (!$this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
            return [];
        }
        $function = $this->reflectionProvider->getFunction($funcCall->name, $scope);
        if ($function->hasSideEffects()->no()) {
            $throwsType = $function->getThrowType();
            if ($throwsType !== null && !$throwsType instanceof \PHPStan\Type\VoidType) {
                return [];
            }
            $functionResult = $scope->getType($funcCall);
            if ($functionResult instanceof \PHPStan\Type\NeverType && $functionResult->isExplicit()) {
                return [];
            }
            if (\in_array($function->getName(), ['PHPStan\\Testing\\assertType', 'PHPStan\\Testing\\assertNativeType', 'PHPStan\\Testing\\assertVariableCertainty'], \true)) {
                return [];
            }
            return [\PHPStan\Rules\RuleErrorBuilder::message(\sprintf('Call to function %s() on a separate line has no effect.', $function->getName()))->build()];
        }
        return [];
    }
}
