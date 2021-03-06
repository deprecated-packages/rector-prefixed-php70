<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210620\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\MethodCallToAnotherMethodCallWithArgumentsRectorTest
 */
final class MethodCallToAnotherMethodCallWithArgumentsRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    const METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS = 'method_call_renames_with_added_arguments';
    /**
     * @var MethodCallToAnotherMethodCallWithArguments[]
     */
    private $methodCallRenamesWithAddedArguments = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns old method call with specific types to new one with arguments', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->setInject();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->addTag('inject');
CODE_SAMPLE
, [self::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => [new \Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey('Nette\\DI\\ServiceDefinition', 'setInject', 'addTag', 'inject')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach ($this->methodCallRenamesWithAddedArguments as $methodCallRenameWithAddedArgument) {
            if (!$this->isObjectType($node->var, $methodCallRenameWithAddedArgument->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $methodCallRenameWithAddedArgument->getOldMethod())) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($methodCallRenameWithAddedArgument->getNewMethod());
            $node->args = $this->nodeFactory->createArgs($methodCallRenameWithAddedArgument->getNewArguments());
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, MethodCallToAnotherMethodCallWithArguments[]> $configuration
     * @return void
     */
    public function configure(array $configuration)
    {
        $methodCallRenamesWithAddedArguments = $configuration[self::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS] ?? [];
        \RectorPrefix20210620\Webmozart\Assert\Assert::allIsInstanceOf($methodCallRenamesWithAddedArguments, \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments::class);
        $this->methodCallRenamesWithAddedArguments = $methodCallRenamesWithAddedArguments;
    }
}
