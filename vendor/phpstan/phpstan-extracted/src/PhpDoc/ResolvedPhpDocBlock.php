<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\Tag\MixinTag;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\PhpDoc\Tag\ReturnTag;
use PHPStan\PhpDoc\Tag\ThrowsTag;
use PHPStan\PhpDoc\Tag\TypeAliasImportTag;
use PHPStan\PhpDoc\Tag\TypeAliasTag;
use PHPStan\PhpDoc\Tag\TypedTag;
use PHPStan\PhpDoc\Tag\VarTag;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
/** @api */
class ResolvedPhpDocBlock
{
    /** @var PhpDocNode */
    private $phpDocNode;
    /** @var PhpDocNode[] */
    private $phpDocNodes;
    /** @var string */
    private $phpDocString;
    /** @var string|null */
    private $filename;
    /** @var NameScope|null */
    private $nameScope = null;
    /** @var TemplateTypeMap */
    private $templateTypeMap;
    /** @var array<string, \PHPStan\PhpDoc\Tag\TemplateTag> */
    private $templateTags;
    /** @var \PHPStan\PhpDoc\PhpDocNodeResolver */
    private $phpDocNodeResolver;
    /** @var array<string|int, \PHPStan\PhpDoc\Tag\VarTag>|false */
    private $varTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\MethodTag>|false */
    private $methodTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\PropertyTag>|false */
    private $propertyTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\ExtendsTag>|false */
    private $extendsTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\ImplementsTag>|false */
    private $implementsTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\UsesTag>|false */
    private $usesTags = \false;
    /** @var array<string, \PHPStan\PhpDoc\Tag\ParamTag>|false */
    private $paramTags = \false;
    /** @var \PHPStan\PhpDoc\Tag\ReturnTag|false|null */
    private $returnTag = \false;
    /** @var \PHPStan\PhpDoc\Tag\ThrowsTag|false|null */
    private $throwsTag = \false;
    /** @var array<MixinTag>|false */
    private $mixinTags = \false;
    /** @var array<TypeAliasTag>|false */
    private $typeAliasTags = \false;
    /** @var array<TypeAliasImportTag>|false */
    private $typeAliasImportTags = \false;
    /** @var \PHPStan\PhpDoc\Tag\DeprecatedTag|false|null */
    private $deprecatedTag = \false;
    /** @var bool|null */
    private $isDeprecated = null;
    /** @var bool|null */
    private $isInternal = null;
    /** @var bool|null */
    private $isFinal = null;
    /** @var bool|'notLoaded'|null */
    private $isPure = 'notLoaded';
    private function __construct()
    {
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode
     * @param string $phpDocString
     * @param string $filename
     * @param \PHPStan\Analyser\NameScope $nameScope
     * @param \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap
     * @param \PHPStan\PhpDoc\Tag\TemplateTag[] $templateTags
     * @param \PHPStan\PhpDoc\PhpDocNodeResolver $phpDocNodeResolver
     * @return self
     */
    public static function create(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, string $phpDocString, string $filename, \PHPStan\Analyser\NameScope $nameScope, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap, array $templateTags, \PHPStan\PhpDoc\PhpDocNodeResolver $phpDocNodeResolver)
    {
        // new property also needs to be added to createEmpty() and merge()
        $self = new self();
        $self->phpDocNode = $phpDocNode;
        $self->phpDocNodes = [$phpDocNode];
        $self->phpDocString = $phpDocString;
        $self->filename = $filename;
        $self->nameScope = $nameScope;
        $self->templateTypeMap = $templateTypeMap;
        $self->templateTags = $templateTags;
        $self->phpDocNodeResolver = $phpDocNodeResolver;
        return $self;
    }
    /**
     * @return $this
     */
    public static function createEmpty()
    {
        // new property also needs to be added to merge()
        $self = new self();
        $self->phpDocString = '/** */';
        $self->phpDocNodes = [];
        $self->filename = null;
        $self->templateTypeMap = \PHPStan\Type\Generic\TemplateTypeMap::createEmpty();
        $self->templateTags = [];
        $self->varTags = [];
        $self->methodTags = [];
        $self->propertyTags = [];
        $self->extendsTags = [];
        $self->implementsTags = [];
        $self->usesTags = [];
        $self->paramTags = [];
        $self->returnTag = null;
        $self->throwsTag = null;
        $self->mixinTags = [];
        $self->typeAliasTags = [];
        $self->typeAliasImportTags = [];
        $self->deprecatedTag = null;
        $self->isDeprecated = \false;
        $self->isInternal = \false;
        $self->isFinal = \false;
        $self->isPure = null;
        return $self;
    }
    /**
     * @param array<int, self> $parents
     * @param array<int, PhpDocBlock> $parentPhpDocBlocks
     * @return self
     */
    public function merge(array $parents, array $parentPhpDocBlocks)
    {
        // new property also needs to be added to createEmpty()
        $result = new self();
        // we will resolve everything on $this here so these properties don't have to be populated
        // skip $result->phpDocNode
        // skip $result->phpDocString - just for stubs
        $phpDocNodes = $this->phpDocNodes;
        foreach ($parents as $parent) {
            foreach ($parent->phpDocNodes as $phpDocNode) {
                $phpDocNodes[] = $phpDocNode;
            }
        }
        $result->phpDocNodes = $phpDocNodes;
        $result->filename = $this->filename;
        // skip $result->nameScope
        $result->templateTypeMap = $this->templateTypeMap;
        $result->templateTags = $this->templateTags;
        // skip $result->phpDocNodeResolver
        $result->varTags = self::mergeVarTags($this->getVarTags(), $parents, $parentPhpDocBlocks);
        $result->methodTags = $this->getMethodTags();
        $result->propertyTags = $this->getPropertyTags();
        $result->extendsTags = $this->getExtendsTags();
        $result->implementsTags = $this->getImplementsTags();
        $result->usesTags = $this->getUsesTags();
        $result->paramTags = self::mergeParamTags($this->getParamTags(), $parents, $parentPhpDocBlocks);
        $result->returnTag = self::mergeReturnTags($this->getReturnTag(), $parents, $parentPhpDocBlocks);
        $result->throwsTag = self::mergeThrowsTags($this->getThrowsTag(), $parents);
        $result->mixinTags = $this->getMixinTags();
        $result->typeAliasTags = $this->getTypeAliasTags();
        $result->typeAliasImportTags = $this->getTypeAliasImportTags();
        $result->deprecatedTag = $this->getDeprecatedTag();
        $result->isDeprecated = $result->deprecatedTag !== null;
        $result->isInternal = $this->isInternal();
        $result->isFinal = $this->isFinal();
        $result->isPure = $this->isPure();
        return $result;
    }
    /**
     * @param array<string, string> $parameterNameMapping
     * @return self
     */
    public function changeParameterNamesByMapping(array $parameterNameMapping)
    {
        $paramTags = $this->getParamTags();
        $newParamTags = [];
        foreach ($paramTags as $key => $paramTag) {
            if (!\array_key_exists($key, $parameterNameMapping)) {
                continue;
            }
            $newParamTags[$parameterNameMapping[$key]] = $paramTag;
        }
        $self = new self();
        $self->phpDocNode = $this->phpDocNode;
        $self->phpDocNodes = $this->phpDocNodes;
        $self->phpDocString = $this->phpDocString;
        $self->filename = $this->filename;
        $self->nameScope = $this->nameScope;
        $self->templateTypeMap = $this->templateTypeMap;
        $self->templateTags = $this->templateTags;
        $self->phpDocNodeResolver = $this->phpDocNodeResolver;
        $self->varTags = $this->varTags;
        $self->methodTags = $this->methodTags;
        $self->propertyTags = $this->propertyTags;
        $self->extendsTags = $this->extendsTags;
        $self->implementsTags = $this->implementsTags;
        $self->usesTags = $this->usesTags;
        $self->paramTags = $newParamTags;
        $self->returnTag = $this->returnTag;
        $self->throwsTag = $this->throwsTag;
        $self->mixinTags = $this->mixinTags;
        $self->typeAliasTags = $this->typeAliasTags;
        $self->typeAliasImportTags = $this->typeAliasImportTags;
        $self->deprecatedTag = $this->deprecatedTag;
        $self->isDeprecated = $this->isDeprecated;
        $self->isInternal = $this->isInternal;
        $self->isFinal = $this->isFinal;
        $self->isPure = $this->isPure;
        return $self;
    }
    public function getPhpDocString() : string
    {
        return $this->phpDocString;
    }
    /**
     * @return PhpDocNode[]
     */
    public function getPhpDocNodes() : array
    {
        return $this->phpDocNodes;
    }
    /**
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }
    private function getNameScope() : \PHPStan\Analyser\NameScope
    {
        return $this->nameScope;
    }
    /**
     * @return \PHPStan\Analyser\NameScope|null
     */
    public function getNullableNameScope()
    {
        return $this->nameScope;
    }
    /**
     * @return array<string|int, \PHPStan\PhpDoc\Tag\VarTag>
     */
    public function getVarTags() : array
    {
        if ($this->varTags === \false) {
            $this->varTags = $this->phpDocNodeResolver->resolveVarTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->varTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\MethodTag>
     */
    public function getMethodTags() : array
    {
        if ($this->methodTags === \false) {
            $this->methodTags = $this->phpDocNodeResolver->resolveMethodTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->methodTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\PropertyTag>
     */
    public function getPropertyTags() : array
    {
        if ($this->propertyTags === \false) {
            $this->propertyTags = $this->phpDocNodeResolver->resolvePropertyTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->propertyTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\TemplateTag>
     */
    public function getTemplateTags() : array
    {
        return $this->templateTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\ExtendsTag>
     */
    public function getExtendsTags() : array
    {
        if ($this->extendsTags === \false) {
            $this->extendsTags = $this->phpDocNodeResolver->resolveExtendsTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->extendsTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\ImplementsTag>
     */
    public function getImplementsTags() : array
    {
        if ($this->implementsTags === \false) {
            $this->implementsTags = $this->phpDocNodeResolver->resolveImplementsTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->implementsTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\UsesTag>
     */
    public function getUsesTags() : array
    {
        if ($this->usesTags === \false) {
            $this->usesTags = $this->phpDocNodeResolver->resolveUsesTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->usesTags;
    }
    /**
     * @return array<string, \PHPStan\PhpDoc\Tag\ParamTag>
     */
    public function getParamTags() : array
    {
        if ($this->paramTags === \false) {
            $this->paramTags = $this->phpDocNodeResolver->resolveParamTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->paramTags;
    }
    /**
     * @return \PHPStan\PhpDoc\Tag\ReturnTag|null
     */
    public function getReturnTag()
    {
        if ($this->returnTag === \false) {
            $this->returnTag = $this->phpDocNodeResolver->resolveReturnTag($this->phpDocNode, $this->getNameScope());
        }
        return $this->returnTag;
    }
    /**
     * @return \PHPStan\PhpDoc\Tag\ThrowsTag|null
     */
    public function getThrowsTag()
    {
        if ($this->throwsTag === \false) {
            $this->throwsTag = $this->phpDocNodeResolver->resolveThrowsTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->throwsTag;
    }
    /**
     * @return array<MixinTag>
     */
    public function getMixinTags() : array
    {
        if ($this->mixinTags === \false) {
            $this->mixinTags = $this->phpDocNodeResolver->resolveMixinTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->mixinTags;
    }
    /**
     * @return array<TypeAliasTag>
     */
    public function getTypeAliasTags() : array
    {
        if ($this->typeAliasTags === \false) {
            $this->typeAliasTags = $this->phpDocNodeResolver->resolveTypeAliasTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->typeAliasTags;
    }
    /**
     * @return array<TypeAliasImportTag>
     */
    public function getTypeAliasImportTags() : array
    {
        if ($this->typeAliasImportTags === \false) {
            $this->typeAliasImportTags = $this->phpDocNodeResolver->resolveTypeAliasImportTags($this->phpDocNode, $this->getNameScope());
        }
        return $this->typeAliasImportTags;
    }
    /**
     * @return \PHPStan\PhpDoc\Tag\DeprecatedTag|null
     */
    public function getDeprecatedTag()
    {
        if ($this->deprecatedTag === \false) {
            $this->deprecatedTag = $this->phpDocNodeResolver->resolveDeprecatedTag($this->phpDocNode, $this->getNameScope());
        }
        return $this->deprecatedTag;
    }
    public function isDeprecated() : bool
    {
        if ($this->isDeprecated === null) {
            $this->isDeprecated = $this->phpDocNodeResolver->resolveIsDeprecated($this->phpDocNode);
        }
        return $this->isDeprecated;
    }
    public function isInternal() : bool
    {
        if ($this->isInternal === null) {
            $this->isInternal = $this->phpDocNodeResolver->resolveIsInternal($this->phpDocNode);
        }
        return $this->isInternal;
    }
    public function isFinal() : bool
    {
        if ($this->isFinal === null) {
            $this->isFinal = $this->phpDocNodeResolver->resolveIsFinal($this->phpDocNode);
        }
        return $this->isFinal;
    }
    public function getTemplateTypeMap() : \PHPStan\Type\Generic\TemplateTypeMap
    {
        return $this->templateTypeMap;
    }
    /**
     * @return bool|null
     */
    public function isPure()
    {
        if ($this->isPure === 'notLoaded') {
            $pure = $this->phpDocNodeResolver->resolveIsPure($this->phpDocNode);
            if ($pure) {
                $this->isPure = \true;
                return $this->isPure;
            } else {
                $impure = $this->phpDocNodeResolver->resolveIsImpure($this->phpDocNode);
                if ($impure) {
                    $this->isPure = \false;
                    return $this->isPure;
                }
            }
            $this->isPure = null;
        }
        return $this->isPure;
    }
    /**
     * @param array<string|int, VarTag> $varTags
     * @param array<int, self> $parents
     * @param array<int, PhpDocBlock> $parentPhpDocBlocks
     * @return array<string|int, VarTag>
     */
    private static function mergeVarTags(array $varTags, array $parents, array $parentPhpDocBlocks) : array
    {
        // Only allow one var tag per comment. Check the parent if child does not have this tag.
        if (\count($varTags) > 0) {
            return $varTags;
        }
        foreach ($parents as $i => $parent) {
            $result = self::mergeOneParentVarTags($parent, $parentPhpDocBlocks[$i]);
            if ($result === null) {
                continue;
            }
            return $result;
        }
        return [];
    }
    /**
     * @param ResolvedPhpDocBlock $parent
     * @param PhpDocBlock $phpDocBlock
     * @return mixed[]|null
     */
    private static function mergeOneParentVarTags($parent, \PHPStan\PhpDoc\PhpDocBlock $phpDocBlock)
    {
        foreach ($parent->getVarTags() as $key => $parentVarTag) {
            return [$key => self::resolveTemplateTypeInTag($parentVarTag, $phpDocBlock)];
        }
        return null;
    }
    /**
     * @param array<string, ParamTag> $paramTags
     * @param array<int, self> $parents
     * @param array<int, PhpDocBlock> $parentPhpDocBlocks
     * @return array<string, ParamTag>
     */
    private static function mergeParamTags(array $paramTags, array $parents, array $parentPhpDocBlocks) : array
    {
        foreach ($parents as $i => $parent) {
            $paramTags = self::mergeOneParentParamTags($paramTags, $parent, $parentPhpDocBlocks[$i]);
        }
        return $paramTags;
    }
    /**
     * @param array<string, ParamTag> $paramTags
     * @param ResolvedPhpDocBlock $parent
     * @param PhpDocBlock $phpDocBlock
     * @return array<string, ParamTag>
     */
    private static function mergeOneParentParamTags(array $paramTags, $parent, \PHPStan\PhpDoc\PhpDocBlock $phpDocBlock) : array
    {
        $parentParamTags = $phpDocBlock->transformArrayKeysWithParameterNameMapping($parent->getParamTags());
        foreach ($parentParamTags as $name => $parentParamTag) {
            if (\array_key_exists($name, $paramTags)) {
                continue;
            }
            $paramTags[$name] = self::resolveTemplateTypeInTag($parentParamTag, $phpDocBlock);
        }
        return $paramTags;
    }
    /**
     * @param ReturnTag|null $returnTag
     * @param array<int, self> $parents
     * @param array<int, PhpDocBlock> $parentPhpDocBlocks
     * @return ReturnTag|Null
     */
    private static function mergeReturnTags($returnTag, array $parents, array $parentPhpDocBlocks)
    {
        if ($returnTag !== null) {
            return $returnTag;
        }
        foreach ($parents as $i => $parent) {
            $result = self::mergeOneParentReturnTag($returnTag, $parent, $parentPhpDocBlocks[$i]);
            if ($result === null) {
                continue;
            }
            return $result;
        }
        return null;
    }
    /**
     * @param $this $parent
     * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
     * @return \PHPStan\PhpDoc\Tag\ReturnTag|null
     */
    private static function mergeOneParentReturnTag($returnTag, $parent, \PHPStan\PhpDoc\PhpDocBlock $phpDocBlock)
    {
        $parentReturnTag = $parent->getReturnTag();
        if ($parentReturnTag === null) {
            return $returnTag;
        }
        $parentType = $parentReturnTag->getType();
        // Each parent would overwrite the previous one except if it returns a less specific type.
        // Do not care for incompatible types as there is a separate rule for that.
        if ($returnTag !== null && $parentType->isSuperTypeOf($returnTag->getType())->yes()) {
            return null;
        }
        return self::resolveTemplateTypeInTag($parentReturnTag->toImplicit(), $phpDocBlock);
    }
    /**
     * @param array<int, self> $parents
     * @param \PHPStan\PhpDoc\Tag\ThrowsTag|null $throwsTag
     * @return \PHPStan\PhpDoc\Tag\ThrowsTag|null
     */
    private static function mergeThrowsTags($throwsTag, array $parents)
    {
        if ($throwsTag !== null) {
            return $throwsTag;
        }
        foreach ($parents as $parent) {
            $result = $parent->getThrowsTag();
            if ($result === null) {
                continue;
            }
            return $result;
        }
        return null;
    }
    /**
     * @template T of \PHPStan\PhpDoc\Tag\TypedTag
     * @param T $tag
     * @param PhpDocBlock $phpDocBlock
     * @return T
     */
    private static function resolveTemplateTypeInTag(\PHPStan\PhpDoc\Tag\TypedTag $tag, \PHPStan\PhpDoc\PhpDocBlock $phpDocBlock) : \PHPStan\PhpDoc\Tag\TypedTag
    {
        $type = \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($tag->getType(), $phpDocBlock->getClassReflection()->getActiveTemplateTypeMap());
        return $tag->withType($type);
    }
}
