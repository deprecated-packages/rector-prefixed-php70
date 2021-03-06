<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210620\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\PropertyFetchToMethodCallRectorTest
 */
final class PropertyFetchToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    const PROPERTIES_TO_METHOD_CALLS = 'properties_to_method_calls';
    /**
     * @var PropertyFetchToMethodCall[]
     */
    private $propertiesToMethodCalls = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $firstConfiguration = [self::PROPERTIES_TO_METHOD_CALLS => [new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('SomeObject', 'property', 'getProperty', 'setProperty')]];
        $secondConfiguration = [self::PROPERTIES_TO_METHOD_CALLS => [new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('SomeObject', 'property', 'getConfig', null, ['someArg'])]];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces properties assign calls be defined methods.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$result = $object->property;
$object->property = $value;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = $object->getProperty();
$object->setProperty($value);
CODE_SAMPLE
, $firstConfiguration), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$result = $object->property;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = $object->getProperty('someArg');
CODE_SAMPLE
, $secondConfiguration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param PropertyFetch|Assign $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\Assign && $node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return $this->processSetter($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return $this->processGetter($node);
        }
        return null;
    }
    /**
     * @param array<string, PropertyFetchToMethodCall[]> $configuration
     * @return void
     */
    public function configure(array $configuration)
    {
        $propertiesToMethodCalls = $configuration[self::PROPERTIES_TO_METHOD_CALLS] ?? [];
        \RectorPrefix20210620\Webmozart\Assert\Assert::allIsInstanceOf($propertiesToMethodCalls, \Rector\Transform\ValueObject\PropertyFetchToMethodCall::class);
        $this->propertiesToMethodCalls = $propertiesToMethodCalls;
    }
    /**
     * @return \PhpParser\Node|null
     */
    private function processSetter(\PhpParser\Node\Expr\Assign $assign)
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;
        $propertyToMethodCall = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if (!$propertyToMethodCall instanceof \Rector\Transform\ValueObject\PropertyFetchToMethodCall) {
            return null;
        }
        if ($propertyToMethodCall->getNewSetMethod() === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $args = $this->nodeFactory->createArgs([$assign->expr]);
        /** @var Variable $variable */
        $variable = $propertyFetchNode->var;
        return $this->nodeFactory->createMethodCall($variable, $propertyToMethodCall->getNewSetMethod(), $args);
    }
    /**
     * @return \PhpParser\Node|null
     */
    private function processGetter(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $propertyToMethodCall = $this->matchPropertyFetchCandidate($propertyFetch);
        if (!$propertyToMethodCall instanceof \Rector\Transform\ValueObject\PropertyFetchToMethodCall) {
            return null;
        }
        // simple method name
        if ($propertyToMethodCall->getNewGetMethod() !== '') {
            $methodCall = $this->nodeFactory->createMethodCall($propertyFetch->var, $propertyToMethodCall->getNewGetMethod());
            if ($propertyToMethodCall->getNewGetArguments() !== []) {
                $args = $this->nodeFactory->createArgs($propertyToMethodCall->getNewGetArguments());
                $methodCall->args = $args;
            }
            return $methodCall;
        }
        return $propertyFetch;
    }
    /**
     * @return \Rector\Transform\ValueObject\PropertyFetchToMethodCall|null
     */
    private function matchPropertyFetchCandidate(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        foreach ($this->propertiesToMethodCalls as $propertyToMethodCall) {
            if (!$this->isObjectType($propertyFetch->var, $propertyToMethodCall->getOldObjectType())) {
                continue;
            }
            if (!$this->isName($propertyFetch, $propertyToMethodCall->getOldProperty())) {
                continue;
            }
            return $propertyToMethodCall;
        }
        return null;
    }
}
