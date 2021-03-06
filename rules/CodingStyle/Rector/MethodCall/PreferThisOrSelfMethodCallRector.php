<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\ValueObject\PreferenceSelfThis;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210620\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    const TYPE_TO_PREFERENCE = 'type_to_preference';
    /**
     * @var string
     */
    const SELF = 'self';
    /**
     * @var array<class-string, string>
     */
    private $typeToPreference = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes $this->... and static:: to self:: or vise versa for given types', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        $this->assertEquals('a', 'a');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        self::assertEquals('a', 'a');
    }
}
CODE_SAMPLE
, [self::TYPE_TO_PREFERENCE => ['PHPUnit\\Framework\\TestCase' => \Rector\CodingStyle\ValueObject\PreferenceSelfThis::PREFER_SELF]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach ($this->typeToPreference as $type => $preference) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            if ($preference === \Rector\CodingStyle\ValueObject\PreferenceSelfThis::PREFER_SELF) {
                return $this->processToSelf($node);
            }
            return $this->processToThis($node);
        }
        return null;
    }
    /**
     * @param array<string, array<class-string, string>> $configuration
     * @return void
     */
    public function configure(array $configuration)
    {
        $typeToPreference = $configuration[self::TYPE_TO_PREFERENCE] ?? [];
        \RectorPrefix20210620\Webmozart\Assert\Assert::allString($typeToPreference);
        foreach ($typeToPreference as $singleTypeToPreference) {
            $this->ensurePreferenceIsValid($singleTypeToPreference);
        }
        $this->typeToPreference = $typeToPreference;
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\StaticCall|null
     */
    private function processToSelf(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\StaticCall && !$this->isNames($node->class, [self::SELF, 'static'])) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && !$this->isName($node->var, 'this')) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        return $this->nodeFactory->createStaticCall(self::SELF, $name, $node->args);
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function processToThis(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->isNames($node->class, [self::SELF, 'static'])) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', $name, $node->args);
    }
    /**
     * @return void
     */
    private function ensurePreferenceIsValid(string $preference)
    {
        if (\in_array($preference, \Rector\CodingStyle\ValueObject\PreferenceSelfThis::ALLOWED_VALUES, \true)) {
            return;
        }
        throw new \Rector\Core\Exception\Configuration\InvalidConfigurationException(\sprintf('Preference configuration "%s" for "%s" is not valid. Use one of "%s"', $preference, self::class, \implode('", "', \Rector\CodingStyle\ValueObject\PreferenceSelfThis::ALLOWED_VALUES)));
    }
}
