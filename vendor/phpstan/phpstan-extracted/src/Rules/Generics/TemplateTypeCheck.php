<?php

declare (strict_types=1);
namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeAliasResolver;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use function array_map;
class TemplateTypeCheck
{
    /** @var \PHPStan\Reflection\ReflectionProvider */
    private $reflectionProvider;
    /** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
    private $classCaseSensitivityCheck;
    /** @var GenericObjectTypeCheck */
    private $genericObjectTypeCheck;
    /** @var TypeAliasResolver */
    private $typeAliasResolver;
    /** @var bool */
    private $checkClassCaseSensitivity;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \PHPStan\Rules\ClassCaseSensitivityCheck $classCaseSensitivityCheck, \PHPStan\Rules\Generics\GenericObjectTypeCheck $genericObjectTypeCheck, \PHPStan\Type\TypeAliasResolver $typeAliasResolver, bool $checkClassCaseSensitivity)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
        $this->genericObjectTypeCheck = $genericObjectTypeCheck;
        $this->typeAliasResolver = $typeAliasResolver;
        $this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
    }
    /**
     * @param \PhpParser\Node $node
     * @param TemplateTypeScope $templateTypeScope
     * @param array<string, \PHPStan\PhpDoc\Tag\TemplateTag> $templateTags
     * @return \PHPStan\Rules\RuleError[]
     */
    public function check(\PhpParser\Node $node, \PHPStan\Type\Generic\TemplateTypeScope $templateTypeScope, array $templateTags, string $sameTemplateTypeNameAsClassMessage, string $sameTemplateTypeNameAsTypeMessage, string $invalidBoundTypeMessage, string $notSupportedBoundMessage) : array
    {
        $messages = [];
        foreach ($templateTags as $templateTag) {
            $templateTagName = $templateTag->getName();
            if ($this->reflectionProvider->hasClass($templateTagName)) {
                $messages[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf($sameTemplateTypeNameAsClassMessage, $templateTagName))->build();
            }
            if ($this->typeAliasResolver->hasTypeAlias($templateTagName, $templateTypeScope->getClassName())) {
                $messages[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf($sameTemplateTypeNameAsTypeMessage, $templateTagName))->build();
            }
            $boundType = $templateTag->getBound();
            foreach ($boundType->getReferencedClasses() as $referencedClass) {
                if ($this->reflectionProvider->hasClass($referencedClass) && !$this->reflectionProvider->getClass($referencedClass)->isTrait()) {
                    continue;
                }
                $messages[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf($invalidBoundTypeMessage, $templateTagName, $referencedClass))->build();
            }
            if ($this->checkClassCaseSensitivity) {
                $classNameNodePairs = \array_map(static function (string $referencedClass) use($node) : ClassNameNodePair {
                    return new \PHPStan\Rules\ClassNameNodePair($referencedClass, $node);
                }, $boundType->getReferencedClasses());
                $messages = \array_merge($messages, $this->classCaseSensitivityCheck->checkClassNames($classNameNodePairs));
            }
            \PHPStan\Type\TypeTraverser::map($templateTag->getBound(), static function (\PHPStan\Type\Type $type, callable $traverse) use(&$messages, $notSupportedBoundMessage, $templateTagName) : Type {
                $boundClass = \get_class($type);
                if ($boundClass === \PHPStan\Type\MixedType::class || $boundClass === \PHPStan\Type\StringType::class || $boundClass === \PHPStan\Type\IntegerType::class || $boundClass === \PHPStan\Type\ObjectWithoutClassType::class || $boundClass === \PHPStan\Type\ObjectType::class || $boundClass === \PHPStan\Type\Generic\GenericObjectType::class || $type instanceof \PHPStan\Type\UnionType || $type instanceof \PHPStan\Type\Generic\TemplateType) {
                    return $traverse($type);
                }
                $messages[] = \PHPStan\Rules\RuleErrorBuilder::message(\sprintf($notSupportedBoundMessage, $templateTagName, $type->describe(\PHPStan\Type\VerbosityLevel::typeOnly())))->build();
                return $type;
            });
            $genericObjectErrors = $this->genericObjectTypeCheck->check($boundType, \sprintf('PHPDoc tag @template %s bound contains generic type %%s but class %%s is not generic.', $templateTagName), \sprintf('PHPDoc tag @template %s bound has type %%s which does not specify all template types of class %%s: %%s', $templateTagName), \sprintf('PHPDoc tag @template %s bound has type %%s which specifies %%d template types, but class %%s supports only %%d: %%s', $templateTagName), \sprintf('Type %%s in generic type %%s in PHPDoc tag @template %s is not subtype of template type %%s of class %%s.', $templateTagName));
            foreach ($genericObjectErrors as $genericObjectError) {
                $messages[] = $genericObjectError;
            }
        }
        return $messages;
    }
}
