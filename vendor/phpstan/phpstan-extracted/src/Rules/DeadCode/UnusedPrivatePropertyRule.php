<?php

declare (strict_types=1);
namespace PHPStan\Rules\DeadCode;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertiesNode;
use PHPStan\Node\Property\PropertyRead;
use PHPStan\Rules\Properties\ReadWritePropertiesExtensionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeUtils;
/**
 * @implements Rule<ClassPropertiesNode>
 */
class UnusedPrivatePropertyRule implements \PHPStan\Rules\Rule
{
    /** @var ReadWritePropertiesExtensionProvider */
    private $extensionProvider;
    /** @var string[] */
    private $alwaysWrittenTags;
    /** @var string[] */
    private $alwaysReadTags;
    /** @var bool */
    private $checkUninitializedProperties;
    /**
     * @param ReadWritePropertiesExtensionProvider $extensionProvider
     * @param string[] $alwaysWrittenTags
     * @param string[] $alwaysReadTags
     */
    public function __construct(\PHPStan\Rules\Properties\ReadWritePropertiesExtensionProvider $extensionProvider, array $alwaysWrittenTags, array $alwaysReadTags, bool $checkUninitializedProperties)
    {
        $this->extensionProvider = $extensionProvider;
        $this->alwaysWrittenTags = $alwaysWrittenTags;
        $this->alwaysReadTags = $alwaysReadTags;
        $this->checkUninitializedProperties = $checkUninitializedProperties;
    }
    public function getNodeType() : string
    {
        return \PHPStan\Node\ClassPropertiesNode::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!$node->getClass() instanceof \PhpParser\Node\Stmt\Class_) {
            return [];
        }
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $classReflection = $scope->getClassReflection();
        $classType = new \PHPStan\Type\ObjectType($classReflection->getName());
        $properties = [];
        foreach ($node->getProperties() as $property) {
            if (!$property->isPrivate()) {
                continue;
            }
            $alwaysRead = \false;
            $alwaysWritten = \false;
            if ($property->getPhpDoc() !== null) {
                $text = $property->getPhpDoc();
                foreach ($this->alwaysReadTags as $tag) {
                    if (\strpos($text, $tag) === \false) {
                        continue;
                    }
                    $alwaysRead = \true;
                    break;
                }
                foreach ($this->alwaysWrittenTags as $tag) {
                    if (\strpos($text, $tag) === \false) {
                        continue;
                    }
                    $alwaysWritten = \true;
                    break;
                }
            }
            $propertyName = $property->getName();
            if (!$alwaysRead || !$alwaysWritten) {
                if (!$classReflection->hasNativeProperty($propertyName)) {
                    continue;
                }
                $propertyReflection = $classReflection->getNativeProperty($propertyName);
                foreach ($this->extensionProvider->getExtensions() as $extension) {
                    if ($alwaysRead && $alwaysWritten) {
                        break;
                    }
                    if (!$alwaysRead && $extension->isAlwaysRead($propertyReflection, $propertyName)) {
                        $alwaysRead = \true;
                    }
                    if ($alwaysWritten || !$extension->isAlwaysWritten($propertyReflection, $propertyName)) {
                        continue;
                    }
                    $alwaysWritten = \true;
                }
            }
            $read = $alwaysRead;
            $written = $alwaysWritten || $property->getDefault() !== null;
            $properties[$propertyName] = ['read' => $read, 'written' => $written, 'node' => $property];
        }
        foreach ($node->getPropertyUsages() as $usage) {
            $fetch = $usage->getFetch();
            if ($fetch->name instanceof \PhpParser\Node\Identifier) {
                $propertyNames = [$fetch->name->toString()];
            } else {
                $propertyNameType = $usage->getScope()->getType($fetch->name);
                $strings = \PHPStan\Type\TypeUtils::getConstantStrings($propertyNameType);
                if (\count($strings) === 0) {
                    return [];
                }
                $propertyNames = \array_map(static function (\PHPStan\Type\Constant\ConstantStringType $type) : string {
                    return $type->getValue();
                }, $strings);
            }
            if ($fetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
                $fetchedOnType = $usage->getScope()->getType($fetch->var);
            } else {
                if (!$fetch->class instanceof \PhpParser\Node\Name) {
                    continue;
                }
                $fetchedOnType = $usage->getScope()->resolveTypeByName($fetch->class);
            }
            if ($classType->isSuperTypeOf($fetchedOnType)->no()) {
                continue;
            }
            if ($fetchedOnType instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            foreach ($propertyNames as $propertyName) {
                if (!\array_key_exists($propertyName, $properties)) {
                    continue;
                }
                if ($usage instanceof \PHPStan\Node\Property\PropertyRead) {
                    $properties[$propertyName]['read'] = \true;
                } else {
                    $properties[$propertyName]['written'] = \true;
                }
            }
        }
        $constructors = [];
        $classReflection = $scope->getClassReflection();
        if ($classReflection->hasConstructor()) {
            $constructors[] = $classReflection->getConstructor()->getName();
        }
        list($uninitializedProperties) = $node->getUninitializedProperties($scope, $constructors, $this->extensionProvider->getExtensions());
        $errors = [];
        foreach ($properties as $name => $data) {
            $propertyNode = $data['node'];
            if ($propertyNode->isStatic()) {
                $propertyName = \sprintf('Static property %s::$%s', $scope->getClassReflection()->getDisplayName(), $name);
            } else {
                $propertyName = \sprintf('Property %s::$%s', $scope->getClassReflection()->getDisplayName(), $name);
            }
            if (!$data['read']) {
                if (!$data['written']) {
                    $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('%s is unused.', $propertyName))->line($propertyNode->getStartLine())->identifier('deadCode.unusedProperty')->metadata(['classOrder' => $node->getClass()->getAttribute('statementOrder'), 'classDepth' => $node->getClass()->getAttribute('statementDepth'), 'classStartLine' => $node->getClass()->getStartLine(), 'propertyName' => $name])->build();
                } else {
                    $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('%s is never read, only written.', $propertyName))->line($propertyNode->getStartLine())->build();
                }
            } elseif (!$data['written'] && (!\array_key_exists($name, $uninitializedProperties) || !$this->checkUninitializedProperties)) {
                $errors[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf('%s is never written, only read.', $propertyName))->line($propertyNode->getStartLine())->build();
            }
        }
        return $errors;
    }
}
