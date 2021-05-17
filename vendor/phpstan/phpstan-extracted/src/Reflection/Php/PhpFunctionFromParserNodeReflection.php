<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VoidType;
class PhpFunctionFromParserNodeReflection implements \PHPStan\Reflection\FunctionReflection
{
    /** @var \PhpParser\Node\FunctionLike */
    private $functionLike;
    /** @var \PHPStan\Type\Generic\TemplateTypeMap */
    private $templateTypeMap;
    /** @var \PHPStan\Type\Type[] */
    private $realParameterTypes;
    /** @var \PHPStan\Type\Type[] */
    private $phpDocParameterTypes;
    /** @var \PHPStan\Type\Type[] */
    private $realParameterDefaultValues;
    /** @var \PHPStan\Type\Type */
    private $realReturnType;
    /** @var \PHPStan\Type\Type|null */
    private $phpDocReturnType;
    /** @var \PHPStan\Type\Type|null */
    private $throwType;
    /** @var string|null */
    private $deprecatedDescription;
    /** @var bool */
    private $isDeprecated;
    /** @var bool */
    private $isInternal;
    /** @var bool */
    private $isFinal;
    /** @var bool|null */
    private $isPure;
    /** @var FunctionVariantWithPhpDocs[]|null */
    private $variants = null;
    /**
     * @param FunctionLike $functionLike
     * @param TemplateTypeMap $templateTypeMap
     * @param \PHPStan\Type\Type[] $realParameterTypes
     * @param \PHPStan\Type\Type[] $phpDocParameterTypes
     * @param \PHPStan\Type\Type[] $realParameterDefaultValues
     * @param Type $realReturnType
     * @param Type|null $phpDocReturnType
     * @param Type|null $throwType
     * @param string|null $deprecatedDescription
     * @param bool $isDeprecated
     * @param bool $isInternal
     * @param bool $isFinal
     * @param bool|null $isPure
     */
    public function __construct(\PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap, array $realParameterTypes, array $phpDocParameterTypes, array $realParameterDefaultValues, \PHPStan\Type\Type $realReturnType, $phpDocReturnType = null, $throwType = null, $deprecatedDescription = null, bool $isDeprecated = \false, bool $isInternal = \false, bool $isFinal = \false, $isPure = null)
    {
        $this->functionLike = $functionLike;
        $this->templateTypeMap = $templateTypeMap;
        $this->realParameterTypes = $realParameterTypes;
        $this->phpDocParameterTypes = $phpDocParameterTypes;
        $this->realParameterDefaultValues = $realParameterDefaultValues;
        $this->realReturnType = $realReturnType;
        $this->phpDocReturnType = $phpDocReturnType;
        $this->throwType = $throwType;
        $this->deprecatedDescription = $deprecatedDescription;
        $this->isDeprecated = $isDeprecated;
        $this->isInternal = $isInternal;
        $this->isFinal = $isFinal;
        $this->isPure = $isPure;
    }
    protected function getFunctionLike() : \PhpParser\Node\FunctionLike
    {
        return $this->functionLike;
    }
    public function getName() : string
    {
        if ($this->functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->functionLike->name->name;
        }
        return (string) $this->functionLike->namespacedName;
    }
    /**
     * @return \PHPStan\Reflection\ParametersAcceptorWithPhpDocs[]
     */
    public function getVariants() : array
    {
        if ($this->variants === null) {
            $this->variants = [new \PHPStan\Reflection\FunctionVariantWithPhpDocs($this->templateTypeMap, null, $this->getParameters(), $this->isVariadic(), $this->getReturnType(), $this->phpDocReturnType ?? new \PHPStan\Type\MixedType(), $this->realReturnType)];
        }
        return $this->variants;
    }
    /**
     * @return \PHPStan\Reflection\ParameterReflectionWithPhpDocs[]
     */
    private function getParameters() : array
    {
        $parameters = [];
        $isOptional = \true;
        /** @var \PhpParser\Node\Param $parameter */
        foreach (\array_reverse($this->functionLike->getParams()) as $parameter) {
            if ($parameter->default === null && !$parameter->variadic) {
                $isOptional = \false;
            }
            if (!$parameter->var instanceof \PhpParser\Node\Expr\Variable || !\is_string($parameter->var->name)) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            $parameters[] = new \PHPStan\Reflection\Php\PhpParameterFromParserNodeReflection($parameter->var->name, $isOptional, $this->realParameterTypes[$parameter->var->name], $this->phpDocParameterTypes[$parameter->var->name] ?? null, $parameter->byRef ? \PHPStan\Reflection\PassedByReference::createCreatesNewVariable() : \PHPStan\Reflection\PassedByReference::createNo(), $this->realParameterDefaultValues[$parameter->var->name] ?? null, $parameter->variadic);
        }
        return \array_reverse($parameters);
    }
    private function isVariadic() : bool
    {
        foreach ($this->functionLike->getParams() as $parameter) {
            if ($parameter->variadic) {
                return \true;
            }
        }
        return \false;
    }
    private function getReturnType() : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypehintHelper::decideType($this->realReturnType, $this->phpDocReturnType);
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        if ($this->isDeprecated) {
            return $this->deprecatedDescription;
        }
        return null;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isDeprecated);
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createFromBoolean($this->isInternal);
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        $finalMethod = \false;
        if ($this->functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $finalMethod = $this->functionLike->isFinal();
        }
        return \PHPStan\TrinaryLogic::createFromBoolean($finalMethod || $this->isFinal);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        return $this->throwType;
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        if ($this->getReturnType() instanceof \PHPStan\Type\VoidType) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        if ($this->isPure !== null) {
            return \PHPStan\TrinaryLogic::createFromBoolean(!$this->isPure);
        }
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    public function isBuiltin() : bool
    {
        return \false;
    }
}
