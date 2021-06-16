<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\Type\CallbackUnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\CallbackUnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\UndecidedComparisonTypeTrait;
/** @api */
class StaticType implements \PHPStan\Type\TypeWithClassName
{
    use NonGenericTypeTrait;
    use UndecidedComparisonTypeTrait;
    /** @var ClassReflection|null */
    private $classReflection;
    /** @var \PHPStan\Type\ObjectType|null */
    private $staticObjectType = null;
    /** @var string */
    private $baseClass;
    /**
     * @api
     * @param string|ClassReflection $classReflection
     */
    public function __construct($classReflection)
    {
        if (\is_string($classReflection)) {
            $broker = \PHPStan\Broker\Broker::getInstance();
            if ($broker->hasClass($classReflection)) {
                $classReflection = $broker->getClass($classReflection);
                $this->classReflection = $classReflection;
                $this->baseClass = $classReflection->getName();
                return;
            }
            $this->classReflection = null;
            $this->baseClass = $classReflection;
            return;
        }
        $this->classReflection = $classReflection;
        $this->baseClass = $classReflection->getName();
    }
    public function getClassName() : string
    {
        return $this->baseClass;
    }
    /**
     * @return \PHPStan\Reflection\ClassReflection|null
     */
    public function getClassReflection()
    {
        return $this->classReflection;
    }
    /**
     * @return \PHPStan\Type\TypeWithClassName|null
     */
    public function getAncestorWithClassName(string $className)
    {
        $ancestor = $this->getStaticObjectType()->getAncestorWithClassName($className);
        if ($ancestor === null) {
            return null;
        }
        return $this->changeBaseClass($ancestor->getClassReflection() ?? $ancestor->getClassName());
    }
    public function getStaticObjectType() : \PHPStan\Type\ObjectType
    {
        if ($this->staticObjectType === null) {
            if ($this->classReflection !== null && $this->classReflection->isGeneric()) {
                $typeMap = $this->classReflection->getActiveTemplateTypeMap()->map(static function (string $name, \PHPStan\Type\Type $type) : Type {
                    return \PHPStan\Type\Generic\TemplateTypeHelper::toArgument($type);
                });
                return $this->staticObjectType = new \PHPStan\Type\Generic\GenericObjectType($this->classReflection->getName(), $this->classReflection->typeMapToList($typeMap));
            }
            return $this->staticObjectType = new \PHPStan\Type\ObjectType($this->baseClass, null, $this->classReflection);
        }
        return $this->staticObjectType;
    }
    /**
     * @return string[]
     */
    public function getReferencedClasses() : array
    {
        return $this->getStaticObjectType()->getReferencedClasses();
    }
    public function getBaseClass() : string
    {
        return $this->baseClass;
    }
    public function accepts(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return \PHPStan\Type\CompoundTypeHelper::accepts($type, $this, $strictTypes);
        }
        if (!$type instanceof static) {
            return \PHPStan\TrinaryLogic::createNo();
        }
        return $this->getStaticObjectType()->accepts($type->getStaticObjectType(), $strictTypes);
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof self) {
            return $this->getStaticObjectType()->isSuperTypeOf($type);
        }
        if ($type instanceof \PHPStan\Type\ObjectWithoutClassType) {
            return \PHPStan\TrinaryLogic::createMaybe();
        }
        if ($type instanceof \PHPStan\Type\ObjectType) {
            $result = $this->getStaticObjectType()->isSuperTypeOf($type);
            $classReflection = $type->getClassReflection();
            if ($result->yes() && $classReflection !== null && $classReflection->isFinal()) {
                return $result;
            }
            return \PHPStan\TrinaryLogic::createMaybe()->and($result);
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOf($this);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function equals(\PHPStan\Type\Type $type) : bool
    {
        if (\get_class($type) !== static::class) {
            return \false;
        }
        /** @var StaticType $type */
        $type = $type;
        return $this->getStaticObjectType()->equals($type->getStaticObjectType());
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return \sprintf('static(%s)', $this->getClassName());
    }
    public function canAccessProperties() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->canAccessProperties();
    }
    public function hasProperty(string $propertyName) : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->hasProperty($propertyName);
    }
    public function getProperty(string $propertyName, \PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : \PHPStan\Reflection\PropertyReflection
    {
        return $this->getUnresolvedPropertyPrototype($propertyName, $scope)->getTransformedProperty();
    }
    public function getUnresolvedPropertyPrototype(string $propertyName, \PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        $staticObject = $this->getStaticObjectType();
        $nakedProperty = $staticObject->getUnresolvedPropertyPrototype($propertyName, $scope)->getNakedProperty();
        $ancestor = $this->getAncestorWithClassName($nakedProperty->getDeclaringClass()->getName());
        $classReflection = null;
        if ($ancestor !== null) {
            $classReflection = $ancestor->getClassReflection();
        }
        if ($classReflection === null) {
            $classReflection = $nakedProperty->getDeclaringClass();
        }
        return new \PHPStan\Reflection\Type\CallbackUnresolvedPropertyPrototypeReflection($nakedProperty, $classReflection, \false, function (\PHPStan\Type\Type $type) use($scope) : Type {
            return $this->transformStaticType($type, $scope);
        });
    }
    public function canCallMethods() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->canCallMethods();
    }
    public function hasMethod(string $methodName) : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->hasMethod($methodName);
    }
    public function getMethod(string $methodName, \PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : \PHPStan\Reflection\MethodReflection
    {
        return $this->getUnresolvedMethodPrototype($methodName, $scope)->getTransformedMethod();
    }
    public function getUnresolvedMethodPrototype(string $methodName, \PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : \PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection
    {
        $staticObject = $this->getStaticObjectType();
        $nakedMethod = $staticObject->getUnresolvedMethodPrototype($methodName, $scope)->getNakedMethod();
        $ancestor = $this->getAncestorWithClassName($nakedMethod->getDeclaringClass()->getName());
        $classReflection = null;
        if ($ancestor !== null) {
            $classReflection = $ancestor->getClassReflection();
        }
        if ($classReflection === null) {
            $classReflection = $nakedMethod->getDeclaringClass();
        }
        return new \PHPStan\Reflection\Type\CallbackUnresolvedMethodPrototypeReflection($nakedMethod, $classReflection, \false, function (\PHPStan\Type\Type $type) use($scope) : Type {
            return $this->transformStaticType($type, $scope);
        });
    }
    private function transformStaticType(\PHPStan\Type\Type $type, \PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, callable $traverse) use($scope) : Type {
            if ($type instanceof \PHPStan\Type\StaticType) {
                $classReflection = $this->classReflection;
                $isFinal = \false;
                if ($classReflection === null) {
                    $classReflection = $this->baseClass;
                } elseif ($scope->isInClass()) {
                    $classReflection = $scope->getClassReflection();
                    $isFinal = $classReflection->isFinal();
                }
                $type = $type->changeBaseClass($classReflection);
                if (!$isFinal) {
                    return $type;
                }
                return $type->getStaticObjectType();
            }
            return $traverse($type);
        });
    }
    public function canAccessConstants() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->canAccessConstants();
    }
    public function hasConstant(string $constantName) : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->hasConstant($constantName);
    }
    public function getConstant(string $constantName) : \PHPStan\Reflection\ConstantReflection
    {
        return $this->getStaticObjectType()->getConstant($constantName);
    }
    /**
     * @param ClassReflection|string $classReflection
     * @return self
     */
    public function changeBaseClass($classReflection)
    {
        return new self($classReflection);
    }
    public function isIterable() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isIterable();
    }
    public function isIterableAtLeastOnce() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isIterableAtLeastOnce();
    }
    public function getIterableKeyType() : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->getIterableKeyType();
    }
    public function getIterableValueType() : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->getIterableValueType();
    }
    public function isOffsetAccessible() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isOffsetAccessible();
    }
    public function hasOffsetValueType(\PHPStan\Type\Type $offsetType) : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->hasOffsetValueType($offsetType);
    }
    public function getOffsetValueType(\PHPStan\Type\Type $offsetType) : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->getOffsetValueType($offsetType);
    }
    /**
     * @param \PHPStan\Type\Type|null $offsetType
     */
    public function setOffsetValueType($offsetType, \PHPStan\Type\Type $valueType) : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->setOffsetValueType($offsetType, $valueType);
    }
    public function isCallable() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isCallable();
    }
    public function isArray() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isArray();
    }
    public function isNumericString() : \PHPStan\TrinaryLogic
    {
        return $this->getStaticObjectType()->isNumericString();
    }
    /**
     * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getCallableParametersAcceptors(\PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : array
    {
        return $this->getStaticObjectType()->getCallableParametersAcceptors($scope);
    }
    public function isCloneable() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createYes();
    }
    public function toNumber() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toString() : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->toString();
    }
    public function toInteger() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toFloat() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toArray() : \PHPStan\Type\Type
    {
        return $this->getStaticObjectType()->toArray();
    }
    public function toBoolean() : \PHPStan\Type\BooleanType
    {
        return $this->getStaticObjectType()->toBoolean();
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        return $this;
    }
    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['baseClass']);
    }
}
