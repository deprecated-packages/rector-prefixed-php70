<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Arguments\ArgumentDefaultValueReplacer;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210620\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\ReplaceArgumentDefaultValueRectorTest
 */
final class ReplaceArgumentDefaultValueRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    const REPLACED_ARGUMENTS = 'replaced_arguments';
    /**
     * @var ReplaceArgumentDefaultValue[]
     */
    private $replacedArguments = [];
    /**
     * @var \Rector\Arguments\ArgumentDefaultValueReplacer
     */
    private $argumentDefaultValueReplacer;
    public function __construct(\Rector\Arguments\ArgumentDefaultValueReplacer $argumentDefaultValueReplacer)
    {
        $this->argumentDefaultValueReplacer = $argumentDefaultValueReplacer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces defined map of arguments in defined methods and their calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(false);'
CODE_SAMPLE
, [self::REPLACED_ARGUMENTS => [new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('SomeExampleClass', 'someMethod', 0, 'SomeClass::OLD_CONSTANT', \false)]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach ($this->replacedArguments as $replacedArgument) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $replacedArgument->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $replacedArgument->getMethod())) {
                continue;
            }
            $this->argumentDefaultValueReplacer->processReplaces($node, $replacedArgument);
        }
        return $node;
    }
    /**
     * @param array<string, ReplaceArgumentDefaultValue[]> $configuration
     * @return void
     */
    public function configure(array $configuration)
    {
        $replacedArguments = $configuration[self::REPLACED_ARGUMENTS] ?? [];
        \RectorPrefix20210620\Webmozart\Assert\Assert::allIsInstanceOf($replacedArguments, \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue::class);
        $this->replacedArguments = $replacedArguments;
    }
}
