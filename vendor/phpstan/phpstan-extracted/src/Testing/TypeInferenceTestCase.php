<?php

declare (strict_types=1);
namespace PHPStan\Testing;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\DirectScopeFactory;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Broker\Broker;
use PHPStan\Cache\Cache;
use PHPStan\DependencyInjection\Type\DynamicThrowTypeExtensionProvider;
use PHPStan\File\FileHelper;
use PHPStan\File\SimpleRelativePathHelper;
use PHPStan\Php\PhpVersion;
use PHPStan\PhpDoc\PhpDocInheritanceResolver;
use PHPStan\PhpDoc\PhpDocNodeResolver;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\ReflectionProvider\DirectReflectionProviderProvider;
use PHPStan\TrinaryLogic;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\VerbosityLevel;
/** @api */
abstract class TypeInferenceTestCase extends \PHPStan\Testing\TestCase
{
    /** @var bool */
    protected $polluteCatchScopeWithTryAssignments = \true;
    /**
     * @param string $file
     * @param callable(\PhpParser\Node, \PHPStan\Analyser\Scope): void $callback
     * @param DynamicMethodReturnTypeExtension[] $dynamicMethodReturnTypeExtensions
     * @param DynamicStaticMethodReturnTypeExtension[] $dynamicStaticMethodReturnTypeExtensions
     * @param \PHPStan\Type\MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
     * @param \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
     * @param string[] $dynamicConstantNames
     * @return void
     */
    public function processFile(string $file, callable $callback, array $dynamicMethodReturnTypeExtensions = [], array $dynamicStaticMethodReturnTypeExtensions = [], array $methodTypeSpecifyingExtensions = [], array $staticMethodTypeSpecifyingExtensions = [], array $dynamicConstantNames = [])
    {
        $phpDocStringResolver = self::getContainer()->getByType(\PHPStan\PhpDoc\PhpDocStringResolver::class);
        $phpDocNodeResolver = self::getContainer()->getByType(\PHPStan\PhpDoc\PhpDocNodeResolver::class);
        $printer = new \PhpParser\PrettyPrinter\Standard();
        $broker = $this->createBroker($dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions);
        \PHPStan\Broker\Broker::registerInstance($broker);
        $typeSpecifier = $this->createTypeSpecifier($printer, $broker, $methodTypeSpecifyingExtensions, $staticMethodTypeSpecifyingExtensions);
        $currentWorkingDirectory = $this->getCurrentWorkingDirectory();
        $fileHelper = new \PHPStan\File\FileHelper($currentWorkingDirectory);
        $fileTypeMapper = new \PHPStan\Type\FileTypeMapper(new \PHPStan\Reflection\ReflectionProvider\DirectReflectionProviderProvider($broker), $this->getParser(), $phpDocStringResolver, $phpDocNodeResolver, $this->createMock(\PHPStan\Cache\Cache::class), new \PHPStan\Broker\AnonymousClassNameHelper($fileHelper, new \PHPStan\File\SimpleRelativePathHelper($currentWorkingDirectory)));
        $phpDocInheritanceResolver = new \PHPStan\PhpDoc\PhpDocInheritanceResolver($fileTypeMapper);
        $resolver = new \PHPStan\Analyser\NodeScopeResolver($broker, self::getReflectors()[0], $this->getClassReflectionExtensionRegistryProvider(), $this->getParser(), $fileTypeMapper, self::getContainer()->getByType(\PHPStan\Php\PhpVersion::class), $phpDocInheritanceResolver, $fileHelper, $typeSpecifier, self::getContainer()->getByType(\PHPStan\DependencyInjection\Type\DynamicThrowTypeExtensionProvider::class), \true, $this->polluteCatchScopeWithTryAssignments, \true, $this->getEarlyTerminatingMethodCalls(), $this->getEarlyTerminatingFunctionCalls(), \true, \true);
        $resolver->setAnalysedFiles(\array_map(static function (string $file) use($fileHelper) : string {
            return $fileHelper->normalizePath($file);
        }, \array_merge([$file], $this->getAdditionalAnalysedFiles())));
        $scopeFactory = $this->createScopeFactory($broker, $typeSpecifier);
        if (\count($dynamicConstantNames) > 0) {
            $reflectionProperty = new \ReflectionProperty(\PHPStan\Analyser\DirectScopeFactory::class, 'dynamicConstantNames');
            $reflectionProperty->setAccessible(\true);
            $reflectionProperty->setValue($scopeFactory, $dynamicConstantNames);
        }
        $scope = $scopeFactory->create(\PHPStan\Analyser\ScopeContext::create($file));
        $resolver->processNodes($this->getParser()->parseFile($file), $scope, $callback);
    }
    /**
     * @api
     * @param string $assertType
     * @param string $file
     * @param mixed ...$args
     * @return void
     */
    public function assertFileAsserts(string $assertType, string $file, ...$args)
    {
        if ($assertType === 'type') {
            $expectedType = $args[0];
            $expected = $expectedType->getValue();
            $actualType = $args[1];
            $actual = $actualType->describe(\PHPStan\Type\VerbosityLevel::precise());
            $this->assertSame($expected, $actual, \sprintf('Expected type %s, got type %s in %s on line %d.', $expected, $actual, $file, $args[2]));
        } elseif ($assertType === 'variableCertainty') {
            $expectedCertainty = $args[0];
            $actualCertainty = $args[1];
            $variableName = $args[2];
            $this->assertTrue($expectedCertainty->equals($actualCertainty), \sprintf('Expected %s, actual certainty of variable $%s is %s', $expectedCertainty->describe(), $variableName, $actualCertainty->describe()));
        }
    }
    /**
     * @api
     * @param string $file
     * @return array<string, mixed[]>
     */
    public function gatherAssertTypes(string $file) : array
    {
        $asserts = [];
        $this->processFile($file, function (\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) use(&$asserts, $file) {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return;
            }
            $nameNode = $node->name;
            if (!$nameNode instanceof \PhpParser\Node\Name) {
                return;
            }
            $functionName = $nameNode->toString();
            if ($functionName === 'PHPStan\\Testing\\assertType') {
                $expectedType = $scope->getType($node->args[0]->value);
                $actualType = $scope->getType($node->args[1]->value);
                $assert = ['type', $file, $expectedType, $actualType, $node->getLine()];
            } elseif ($functionName === 'PHPStan\\Testing\\assertNativeType') {
                $nativeScope = $scope->doNotTreatPhpDocTypesAsCertain();
                $expectedType = $nativeScope->getNativeType($node->args[0]->value);
                $actualType = $nativeScope->getNativeType($node->args[1]->value);
                $assert = ['type', $file, $expectedType, $actualType, $node->getLine()];
            } elseif ($functionName === 'PHPStan\\Testing\\assertVariableCertainty') {
                $certainty = $node->args[0]->value;
                if (!$certainty instanceof \PhpParser\Node\Expr\StaticCall) {
                    $this->fail(\sprintf('First argument of %s() must be TrinaryLogic call', $functionName));
                }
                if (!$certainty->class instanceof \PhpParser\Node\Name) {
                    $this->fail(\sprintf('ERROR: Invalid TrinaryLogic call.'));
                }
                if ($certainty->class->toString() !== 'PHPStan\\TrinaryLogic') {
                    $this->fail(\sprintf('ERROR: Invalid TrinaryLogic call.'));
                }
                if (!$certainty->name instanceof \PhpParser\Node\Identifier) {
                    $this->fail(\sprintf('ERROR: Invalid TrinaryLogic call.'));
                }
                // @phpstan-ignore-next-line
                $expectedertaintyValue = \PHPStan\TrinaryLogic::{$certainty->name->toString()}();
                $variable = $node->args[1]->value;
                if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
                    $this->fail(\sprintf('ERROR: Invalid assertVariableCertainty call.'));
                }
                if (!\is_string($variable->name)) {
                    $this->fail(\sprintf('ERROR: Invalid assertVariableCertainty call.'));
                }
                $actualCertaintyValue = $scope->hasVariableType($variable->name);
                $assert = ['variableCertainty', $file, $expectedertaintyValue, $actualCertaintyValue, $variable->name];
            } else {
                return;
            }
            if (\count($node->args) !== 2) {
                $this->fail(\sprintf('ERROR: Wrong %s() call on line %d.', $functionName, $node->getLine()));
            }
            $asserts[$file . ':' . $node->getLine()] = $assert;
        });
        return $asserts;
    }
    /** @return string[] */
    protected function getAdditionalAnalysedFiles() : array
    {
        return [];
    }
    /** @return string[][] */
    protected function getEarlyTerminatingMethodCalls() : array
    {
        return [];
    }
    /** @return string[] */
    protected function getEarlyTerminatingFunctionCalls() : array
    {
        return [];
    }
}
