<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
class IntersectionTypeMethodReflection implements \PHPStan\Reflection\MethodReflection
{
    /** @var string */
    private $methodName;
    /** @var MethodReflection[] */
    private $methods;
    /**
     * @param string $methodName
     * @param \PHPStan\Reflection\MethodReflection[] $methods
     */
    public function __construct(string $methodName, array $methods)
    {
        $this->methodName = $methodName;
        $this->methods = $methods;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->methods[0]->getDeclaringClass();
    }
    public function isStatic() : bool
    {
        foreach ($this->methods as $method) {
            if ($method->isStatic()) {
                return \true;
            }
        }
        return \false;
    }
    public function isPrivate() : bool
    {
        foreach ($this->methods as $method) {
            if (!$method->isPrivate()) {
                return \false;
            }
        }
        return \true;
    }
    public function isPublic() : bool
    {
        foreach ($this->methods as $method) {
            if ($method->isPublic()) {
                return \true;
            }
        }
        return \false;
    }
    public function getName() : string
    {
        return $this->methodName;
    }
    public function getPrototype() : \PHPStan\Reflection\ClassMemberReflection
    {
        return $this;
    }
    public function getVariants() : array
    {
        $returnType = \PHPStan\Type\TypeCombinator::intersect(...\array_map(static function (\PHPStan\Reflection\MethodReflection $method) : Type {
            return \PHPStan\Type\TypeCombinator::intersect(...\array_map(static function (\PHPStan\Reflection\ParametersAcceptor $acceptor) : Type {
                return $acceptor->getReturnType();
            }, $method->getVariants()));
        }, $this->methods));
        return \array_map(static function (\PHPStan\Reflection\ParametersAcceptor $acceptor) use($returnType) : ParametersAcceptor {
            return new \PHPStan\Reflection\FunctionVariant($acceptor->getTemplateTypeMap(), $acceptor->getResolvedTemplateTypeMap(), $acceptor->getParameters(), $acceptor->isVariadic(), $returnType);
        }, $this->methods[0]->getVariants());
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\MethodReflection $method) : TrinaryLogic {
            return $method->isDeprecated();
        }, $this->methods));
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        $descriptions = [];
        foreach ($this->methods as $method) {
            if (!$method->isDeprecated()->yes()) {
                continue;
            }
            $description = $method->getDeprecatedDescription();
            if ($description === null) {
                continue;
            }
            $descriptions[] = $description;
        }
        if (\count($descriptions) === 0) {
            return null;
        }
        return \implode(' ', $descriptions);
    }
    public function isFinal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\MethodReflection $method) : TrinaryLogic {
            return $method->isFinal();
        }, $this->methods));
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\MethodReflection $method) : TrinaryLogic {
            return $method->isInternal();
        }, $this->methods));
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getThrowType()
    {
        $types = [];
        foreach ($this->methods as $method) {
            $type = $method->getThrowType();
            if ($type === null) {
                continue;
            }
            $types[] = $type;
        }
        if (\count($types) === 0) {
            return null;
        }
        return \PHPStan\Type\TypeCombinator::intersect(...$types);
    }
    public function hasSideEffects() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\MethodReflection $method) : TrinaryLogic {
            return $method->hasSideEffects();
        }, $this->methods));
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return null;
    }
}
