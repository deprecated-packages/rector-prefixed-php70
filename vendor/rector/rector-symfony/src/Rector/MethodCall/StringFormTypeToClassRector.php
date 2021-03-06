<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\StringFormTypeToClassRector\StringFormTypeToClassRectorTest
 */
final class StringFormTypeToClassRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    const DESCRIPTION = 'Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, ...);"';
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @var \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(self::DESCRIPTION, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', 'form.type.text');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
CODE_SAMPLE
)]);
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
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        // not a string
        if (!$node->args[1]->value instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        /** @var String_ $stringNode */
        $stringNode = $node->args[1]->value;
        // not a form type string
        $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringNode->value);
        if ($formClass === null) {
            return null;
        }
        $node->args[1]->value = $this->nodeFactory->createClassConstReference($formClass);
        return $node;
    }
}
