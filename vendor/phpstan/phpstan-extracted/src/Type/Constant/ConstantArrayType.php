<?php

declare (strict_types=1);
namespace PHPStan\Type\Constant;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\InaccessibleMethod;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\Generic\TemplateMixedType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use function array_unique;
/**
 * @api
 */
class ConstantArrayType extends \PHPStan\Type\ArrayType implements \PHPStan\Type\ConstantType
{
    const DESCRIBE_LIMIT = 8;
    /** @var array<int, ConstantIntegerType|ConstantStringType> */
    private $keyTypes;
    /** @var array<int, Type> */
    private $valueTypes;
    /** @var int */
    private $nextAutoIndex;
    /** @var int[] */
    private $optionalKeys;
    /** @var self[]|null */
    private $allArrays = null;
    /**
     * @api
     * @param array<int, ConstantIntegerType|ConstantStringType> $keyTypes
     * @param array<int, Type> $valueTypes
     * @param int $nextAutoIndex
     * @param int[] $optionalKeys
     */
    public function __construct(array $keyTypes, array $valueTypes, int $nextAutoIndex = 0, array $optionalKeys = [])
    {
        \assert(\count($keyTypes) === \count($valueTypes));
        parent::__construct(\count($keyTypes) > 0 ? \PHPStan\Type\TypeCombinator::union(...$keyTypes) : new \PHPStan\Type\NeverType(), \count($valueTypes) > 0 ? \PHPStan\Type\TypeCombinator::union(...$valueTypes) : new \PHPStan\Type\NeverType());
        $this->keyTypes = $keyTypes;
        $this->valueTypes = $valueTypes;
        $this->nextAutoIndex = $nextAutoIndex;
        $this->optionalKeys = $optionalKeys;
    }
    public function isEmpty() : bool
    {
        return \count($this->keyTypes) === 0;
    }
    public function getNextAutoIndex() : int
    {
        return $this->nextAutoIndex;
    }
    /**
     * @return int[]
     */
    public function getOptionalKeys() : array
    {
        return $this->optionalKeys;
    }
    /**
     * @return self[]
     */
    public function getAllArrays() : array
    {
        if ($this->allArrays !== null) {
            return $this->allArrays;
        }
        if (\count($this->optionalKeys) <= 10) {
            $optionalKeysCombinations = $this->powerSet($this->optionalKeys);
        } else {
            $optionalKeysCombinations = [[], $this->optionalKeys];
        }
        $requiredKeys = [];
        foreach (\array_keys($this->keyTypes) as $i) {
            if (\in_array($i, $this->optionalKeys, \true)) {
                continue;
            }
            $requiredKeys[] = $i;
        }
        $arrays = [];
        foreach ($optionalKeysCombinations as $combination) {
            $keys = \array_merge($requiredKeys, $combination);
            $builder = \PHPStan\Type\Constant\ConstantArrayTypeBuilder::createEmpty();
            foreach ($keys as $i) {
                $builder->setOffsetValueType($this->keyTypes[$i], $this->valueTypes[$i]);
            }
            $array = $builder->getArray();
            if (!$array instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            $arrays[] = $array;
        }
        return $this->allArrays = $arrays;
    }
    /**
     * @template T
     * @param T[] $in
     * @return T[][]
     */
    private function powerSet(array $in) : array
    {
        $count = \count($in);
        $members = \pow(2, $count);
        $return = [];
        for ($i = 0; $i < $members; $i++) {
            $b = \sprintf('%0' . $count . 'b', $i);
            $out = [];
            for ($j = 0; $j < $count; $j++) {
                if ($b[$j] !== '1') {
                    continue;
                }
                $out[] = $in[$j];
            }
            $return[] = $out;
        }
        return $return;
    }
    public function getKeyType() : \PHPStan\Type\Type
    {
        if (\count($this->keyTypes) > 1) {
            return new \PHPStan\Type\UnionType($this->keyTypes);
        }
        return parent::getKeyType();
    }
    /**
     * @return array<int, ConstantIntegerType|ConstantStringType>
     */
    public function getKeyTypes() : array
    {
        return $this->keyTypes;
    }
    /**
     * @return array<int, Type>
     */
    public function getValueTypes() : array
    {
        return $this->valueTypes;
    }
    public function isOptionalKey(int $i) : bool
    {
        return \in_array($i, $this->optionalKeys, \true);
    }
    public function accepts(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof \PHPStan\Type\MixedType && !$type instanceof \PHPStan\Type\Generic\TemplateMixedType) {
            return $type->isAcceptedBy($this, $strictTypes);
        }
        if ($type instanceof self && \count($this->keyTypes) === 0) {
            return \PHPStan\TrinaryLogic::createFromBoolean(\count($type->keyTypes) === 0);
        }
        $result = \PHPStan\TrinaryLogic::createYes();
        foreach ($this->keyTypes as $i => $keyType) {
            $valueType = $this->valueTypes[$i];
            $hasOffset = $type->hasOffsetValueType($keyType);
            if ($hasOffset->no()) {
                if ($this->isOptionalKey($i)) {
                    continue;
                }
                return $hasOffset;
            }
            if ($hasOffset->maybe() && $this->isOptionalKey($i)) {
                $hasOffset = \PHPStan\TrinaryLogic::createYes();
            }
            $result = $result->and($hasOffset);
            $otherValueType = $type->getOffsetValueType($keyType);
            $acceptsValue = $valueType->accepts($otherValueType, $strictTypes);
            if ($acceptsValue->no()) {
                return $acceptsValue;
            }
            $result = $result->and($acceptsValue);
        }
        return $result->and($type->isArray());
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        if ($type instanceof self) {
            if (\count($this->keyTypes) === 0) {
                if (\count($type->keyTypes) > 0) {
                    if (\count($type->optionalKeys) > 0) {
                        return \PHPStan\TrinaryLogic::createMaybe();
                    }
                    return \PHPStan\TrinaryLogic::createNo();
                }
                return \PHPStan\TrinaryLogic::createYes();
            }
            $results = [];
            foreach ($this->keyTypes as $i => $keyType) {
                $hasOffset = $type->hasOffsetValueType($keyType);
                if ($hasOffset->no()) {
                    if (!$this->isOptionalKey($i)) {
                        return \PHPStan\TrinaryLogic::createNo();
                    }
                    $results[] = \PHPStan\TrinaryLogic::createMaybe();
                    continue;
                }
                $results[] = $this->valueTypes[$i]->isSuperTypeOf($type->getOffsetValueType($keyType));
            }
            return \PHPStan\TrinaryLogic::createYes()->and(...$results);
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            $result = \PHPStan\TrinaryLogic::createMaybe();
            if (\count($this->keyTypes) === 0) {
                return $result;
            }
            return $result->and($this->getKeyType()->isSuperTypeOf($type->getKeyType()), $this->getItemType()->isSuperTypeOf($type->getItemType()));
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOf($this);
        }
        return \PHPStan\TrinaryLogic::createNo();
    }
    public function equals(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof self) {
            return \false;
        }
        if (\count($this->keyTypes) !== \count($type->keyTypes)) {
            return \false;
        }
        foreach ($this->keyTypes as $i => $keyType) {
            $valueType = $this->valueTypes[$i];
            if (!$valueType->equals($type->valueTypes[$i])) {
                return \false;
            }
            if (!$keyType->equals($type->keyTypes[$i])) {
                return \false;
            }
        }
        if ($this->optionalKeys !== $type->optionalKeys) {
            return \false;
        }
        return \true;
    }
    public function isCallable() : \PHPStan\TrinaryLogic
    {
        $typeAndMethod = $this->findTypeAndMethodName();
        if ($typeAndMethod === null) {
            return \PHPStan\TrinaryLogic::createNo();
        }
        return $typeAndMethod->getCertainty();
    }
    /**
     * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getCallableParametersAcceptors(\PHPStan\Reflection\ClassMemberAccessAnswerer $scope) : array
    {
        $typeAndMethodName = $this->findTypeAndMethodName();
        if ($typeAndMethodName === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        if ($typeAndMethodName->isUnknown() || !$typeAndMethodName->getCertainty()->yes()) {
            return [new \PHPStan\Reflection\TrivialParametersAcceptor()];
        }
        $method = $typeAndMethodName->getType()->getMethod($typeAndMethodName->getMethod(), $scope);
        if (!$scope->canCallMethod($method)) {
            return [new \PHPStan\Reflection\InaccessibleMethod($method)];
        }
        return $method->getVariants();
    }
    /**
     * @return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod|null
     */
    public function findTypeAndMethodName()
    {
        if (\count($this->keyTypes) !== 2) {
            return null;
        }
        if ($this->keyTypes[0]->isSuperTypeOf(new \PHPStan\Type\Constant\ConstantIntegerType(0))->no()) {
            return null;
        }
        if ($this->keyTypes[1]->isSuperTypeOf(new \PHPStan\Type\Constant\ConstantIntegerType(1))->no()) {
            return null;
        }
        list($classOrObject, $method) = $this->valueTypes;
        if (!$method instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
        }
        if ($classOrObject instanceof \PHPStan\Type\Constant\ConstantStringType) {
            $broker = \PHPStan\Broker\Broker::getInstance();
            if (!$broker->hasClass($classOrObject->getValue())) {
                return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
            }
            $type = new \PHPStan\Type\ObjectType($broker->getClass($classOrObject->getValue())->getName());
        } elseif ($classOrObject instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            $type = $classOrObject->getGenericType();
        } elseif ((new \PHPStan\Type\ObjectWithoutClassType())->isSuperTypeOf($classOrObject)->yes()) {
            $type = $classOrObject;
        } else {
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
        }
        $has = $type->hasMethod($method->getValue());
        if (!$has->no()) {
            if ($this->isOptionalKey(0) || $this->isOptionalKey(1)) {
                $has = $has->and(\PHPStan\TrinaryLogic::createMaybe());
            }
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createConcrete($type, $method->getValue(), $has);
        }
        return null;
    }
    public function hasOffsetValueType(\PHPStan\Type\Type $offsetType) : \PHPStan\TrinaryLogic
    {
        $offsetType = \PHPStan\Type\ArrayType::castToArrayKeyType($offsetType);
        if ($offsetType instanceof \PHPStan\Type\UnionType) {
            $results = [];
            foreach ($offsetType->getTypes() as $innerType) {
                $results[] = $this->hasOffsetValueType($innerType);
            }
            return \PHPStan\TrinaryLogic::extremeIdentity(...$results);
        }
        $result = \PHPStan\TrinaryLogic::createNo();
        foreach ($this->keyTypes as $i => $keyType) {
            if ($keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType && $offsetType instanceof \PHPStan\Type\StringType && !$offsetType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return \PHPStan\TrinaryLogic::createMaybe();
            }
            $has = $keyType->isSuperTypeOf($offsetType);
            if ($has->yes()) {
                if ($this->isOptionalKey($i)) {
                    return \PHPStan\TrinaryLogic::createMaybe();
                }
                return \PHPStan\TrinaryLogic::createYes();
            }
            if (!$has->maybe()) {
                continue;
            }
            $result = \PHPStan\TrinaryLogic::createMaybe();
        }
        return $result;
    }
    public function getOffsetValueType(\PHPStan\Type\Type $offsetType) : \PHPStan\Type\Type
    {
        $offsetType = \PHPStan\Type\ArrayType::castToArrayKeyType($offsetType);
        $matchingValueTypes = [];
        foreach ($this->keyTypes as $i => $keyType) {
            if ($keyType->isSuperTypeOf($offsetType)->no()) {
                continue;
            }
            $matchingValueTypes[] = $this->valueTypes[$i];
        }
        if (\count($matchingValueTypes) > 0) {
            $type = \PHPStan\Type\TypeCombinator::union(...$matchingValueTypes);
            if ($type instanceof \PHPStan\Type\ErrorType) {
                return new \PHPStan\Type\MixedType();
            }
            return $type;
        }
        return new \PHPStan\Type\ErrorType();
        // undefined offset
    }
    /**
     * @param bool $unionValues
     * @param \PHPStan\Type\Type|null $offsetType
     */
    public function setOffsetValueType($offsetType, \PHPStan\Type\Type $valueType, $unionValues = \false) : \PHPStan\Type\Type
    {
        $builder = \PHPStan\Type\Constant\ConstantArrayTypeBuilder::createFromConstantArray($this);
        $builder->setOffsetValueType($offsetType, $valueType);
        return $builder->getArray();
    }
    public function unsetOffset(\PHPStan\Type\Type $offsetType) : \PHPStan\Type\Type
    {
        $offsetType = \PHPStan\Type\ArrayType::castToArrayKeyType($offsetType);
        if ($offsetType instanceof \PHPStan\Type\Constant\ConstantIntegerType || $offsetType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            foreach ($this->keyTypes as $i => $keyType) {
                if ($keyType->getValue() === $offsetType->getValue()) {
                    $keyTypes = $this->keyTypes;
                    unset($keyTypes[$i]);
                    $valueTypes = $this->valueTypes;
                    unset($valueTypes[$i]);
                    $newKeyTypes = [];
                    $newValueTypes = [];
                    $newOptionalKeys = [];
                    $k = 0;
                    foreach ($keyTypes as $j => $newKeyType) {
                        $newKeyTypes[] = $newKeyType;
                        $newValueTypes[] = $valueTypes[$j];
                        if (\in_array($j, $this->optionalKeys, \true)) {
                            $newOptionalKeys[] = $k;
                        }
                        $k++;
                    }
                    return new self($newKeyTypes, $newValueTypes, $this->nextAutoIndex, $newOptionalKeys);
                }
            }
        }
        $arrays = [];
        foreach ($this->getAllArrays() as $tmp) {
            $arrays[] = new self($tmp->keyTypes, $tmp->valueTypes, $tmp->nextAutoIndex, \array_keys($tmp->keyTypes));
        }
        return \PHPStan\Type\TypeUtils::generalizeType(\PHPStan\Type\TypeCombinator::union(...$arrays));
    }
    public function isIterableAtLeastOnce() : \PHPStan\TrinaryLogic
    {
        $keysCount = \count($this->keyTypes);
        if ($keysCount === 0) {
            return \PHPStan\TrinaryLogic::createNo();
        }
        $optionalKeysCount = \count($this->optionalKeys);
        if ($optionalKeysCount === 0) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        if ($optionalKeysCount < $keysCount) {
            return \PHPStan\TrinaryLogic::createYes();
        }
        return \PHPStan\TrinaryLogic::createMaybe();
    }
    /**
     * @return $this
     */
    public function removeLast()
    {
        if (\count($this->keyTypes) === 0) {
            return $this;
        }
        $i = \count($this->keyTypes) - 1;
        $keyTypes = $this->keyTypes;
        $valueTypes = $this->valueTypes;
        $optionalKeys = $this->optionalKeys;
        unset($optionalKeys[$i]);
        $removedKeyType = \array_pop($keyTypes);
        \array_pop($valueTypes);
        $nextAutoindex = $removedKeyType instanceof \PHPStan\Type\Constant\ConstantIntegerType ? $removedKeyType->getValue() : $this->nextAutoIndex;
        return new self($keyTypes, $valueTypes, $nextAutoindex, \array_values($optionalKeys));
    }
    public function removeFirst() : \PHPStan\Type\ArrayType
    {
        $builder = \PHPStan\Type\Constant\ConstantArrayTypeBuilder::createEmpty();
        foreach ($this->keyTypes as $i => $keyType) {
            if ($i === 0) {
                continue;
            }
            $valueType = $this->valueTypes[$i];
            if ($keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                $keyType = null;
            }
            $builder->setOffsetValueType($keyType, $valueType);
        }
        return $builder->getArray();
    }
    /**
     * @return $this
     * @param int|null $limit
     */
    public function slice(int $offset, $limit, bool $preserveKeys = \false)
    {
        if (\count($this->keyTypes) === 0) {
            return $this;
        }
        $keyTypes = \array_slice($this->keyTypes, $offset, $limit);
        $valueTypes = \array_slice($this->valueTypes, $offset, $limit);
        if (!$preserveKeys) {
            $i = 0;
            /** @var array<int, ConstantIntegerType|ConstantStringType> $keyTypes */
            $keyTypes = \array_map(static function ($keyType) use(&$i) {
                if ($keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                    $i++;
                    return new \PHPStan\Type\Constant\ConstantIntegerType($i - 1);
                }
                return $keyType;
            }, $keyTypes);
        }
        /** @var int|float $nextAutoIndex */
        $nextAutoIndex = 0;
        foreach ($keyTypes as $keyType) {
            if (!$keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                continue;
            }
            /** @var int|float $nextAutoIndex */
            $nextAutoIndex = \max($nextAutoIndex, $keyType->getValue() + 1);
        }
        return new self($keyTypes, $valueTypes, (int) $nextAutoIndex, []);
    }
    public function toBoolean() : \PHPStan\Type\BooleanType
    {
        return $this->count()->toBoolean();
    }
    public function generalize() : \PHPStan\Type\Type
    {
        if (\count($this->keyTypes) === 0) {
            return $this;
        }
        $arrayType = new \PHPStan\Type\ArrayType(\PHPStan\Type\TypeUtils::generalizeType($this->getKeyType()), \PHPStan\Type\TypeUtils::generalizeType($this->getItemType()));
        if (\count($this->keyTypes) > \count($this->optionalKeys)) {
            return \PHPStan\Type\TypeCombinator::intersect($arrayType, new \PHPStan\Type\Accessory\NonEmptyArrayType());
        }
        return $arrayType;
    }
    /**
     * @return self
     */
    public function generalizeValues() : \PHPStan\Type\ArrayType
    {
        $valueTypes = [];
        foreach ($this->valueTypes as $valueType) {
            $valueTypes[] = \PHPStan\Type\TypeUtils::generalizeType($valueType);
        }
        return new self($this->keyTypes, $valueTypes, $this->nextAutoIndex, $this->optionalKeys);
    }
    /**
     * @return self
     */
    public function getKeysArray() : \PHPStan\Type\ArrayType
    {
        $keyTypes = [];
        $valueTypes = [];
        $optionalKeys = [];
        $autoIndex = 0;
        foreach ($this->keyTypes as $i => $keyType) {
            $keyTypes[] = new \PHPStan\Type\Constant\ConstantIntegerType($i);
            $valueTypes[] = $keyType;
            $autoIndex++;
            if (!$this->isOptionalKey($i)) {
                continue;
            }
            $optionalKeys[] = $i;
        }
        return new self($keyTypes, $valueTypes, $autoIndex, $optionalKeys);
    }
    /**
     * @return self
     */
    public function getValuesArray() : \PHPStan\Type\ArrayType
    {
        $keyTypes = [];
        $valueTypes = [];
        $optionalKeys = [];
        $autoIndex = 0;
        foreach ($this->valueTypes as $i => $valueType) {
            $keyTypes[] = new \PHPStan\Type\Constant\ConstantIntegerType($i);
            $valueTypes[] = $valueType;
            $autoIndex++;
            if (!$this->isOptionalKey($i)) {
                continue;
            }
            $optionalKeys[] = $i;
        }
        return new self($keyTypes, $valueTypes, $autoIndex, $optionalKeys);
    }
    public function count() : \PHPStan\Type\Type
    {
        $optionalKeysCount = \count($this->optionalKeys);
        $totalKeysCount = \count($this->getKeyTypes());
        if ($optionalKeysCount === 0) {
            return new \PHPStan\Type\Constant\ConstantIntegerType($totalKeysCount);
        }
        return \PHPStan\Type\IntegerRangeType::fromInterval($totalKeysCount - $optionalKeysCount, $totalKeysCount);
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        $describeValue = function (bool $truncate) use($level) : string {
            $items = [];
            $values = [];
            $exportValuesOnly = \true;
            foreach ($this->keyTypes as $i => $keyType) {
                $valueType = $this->valueTypes[$i];
                if ($keyType->getValue() !== $i) {
                    $exportValuesOnly = \false;
                }
                $isOptional = $this->isOptionalKey($i);
                if ($isOptional) {
                    $exportValuesOnly = \false;
                }
                $items[] = \sprintf('%s%s => %s', $isOptional ? '?' : '', \var_export($keyType->getValue(), \true), $valueType->describe($level));
                $values[] = $valueType->describe($level);
            }
            $append = '';
            if ($truncate && \count($items) > self::DESCRIBE_LIMIT) {
                $items = \array_slice($items, 0, self::DESCRIBE_LIMIT);
                $values = \array_slice($values, 0, self::DESCRIBE_LIMIT);
                $append = ', ...';
            }
            return \sprintf('array(%s%s)', \implode(', ', $exportValuesOnly ? $values : $items), $append);
        };
        return $level->handle(function () use($level) : string {
            return parent::describe($level);
        }, static function () use($describeValue) : string {
            return $describeValue(\true);
        }, static function () use($describeValue) : string {
            return $describeValue(\false);
        });
    }
    public function inferTemplateTypes(\PHPStan\Type\Type $receivedType) : \PHPStan\Type\Generic\TemplateTypeMap
    {
        if ($receivedType instanceof \PHPStan\Type\UnionType || $receivedType instanceof \PHPStan\Type\IntersectionType) {
            return $receivedType->inferTemplateTypesOn($this);
        }
        if ($receivedType instanceof self) {
            $typeMap = \PHPStan\Type\Generic\TemplateTypeMap::createEmpty();
            foreach ($this->keyTypes as $i => $keyType) {
                $valueType = $this->valueTypes[$i];
                if ($receivedType->hasOffsetValueType($keyType)->no()) {
                    continue;
                }
                $receivedValueType = $receivedType->getOffsetValueType($keyType);
                $typeMap = $typeMap->union($valueType->inferTemplateTypes($receivedValueType));
            }
            return $typeMap;
        }
        return parent::inferTemplateTypes($receivedType);
    }
    public function getReferencedTemplateTypes(\PHPStan\Type\Generic\TemplateTypeVariance $positionVariance) : array
    {
        $variance = $positionVariance->compose(\PHPStan\Type\Generic\TemplateTypeVariance::createInvariant());
        $references = [];
        foreach ($this->keyTypes as $type) {
            foreach ($type->getReferencedTemplateTypes($variance) as $reference) {
                $references[] = $reference;
            }
        }
        foreach ($this->valueTypes as $type) {
            foreach ($type->getReferencedTemplateTypes($variance) as $reference) {
                $references[] = $reference;
            }
        }
        return $references;
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $valueTypes = [];
        $stillOriginal = \true;
        foreach ($this->valueTypes as $valueType) {
            $transformedValueType = $cb($valueType);
            if ($transformedValueType !== $valueType) {
                $stillOriginal = \false;
            }
            $valueTypes[] = $transformedValueType;
        }
        if ($stillOriginal) {
            return $this;
        }
        return new self($this->keyTypes, $valueTypes, $this->nextAutoIndex, $this->optionalKeys);
    }
    /**
     * @param $this $otherArray
     */
    public function isKeysSupersetOf($otherArray) : bool
    {
        if (\count($this->keyTypes) === 0) {
            return \count($otherArray->keyTypes) === 0;
        }
        if (\count($otherArray->keyTypes) === 0) {
            return \false;
        }
        $otherKeys = $otherArray->keyTypes;
        foreach ($this->keyTypes as $keyType) {
            foreach ($otherArray->keyTypes as $j => $otherKeyType) {
                if (!$keyType->equals($otherKeyType)) {
                    continue;
                }
                unset($otherKeys[$j]);
                continue 2;
            }
        }
        return \count($otherKeys) === 0;
    }
    /**
     * @param $this $otherArray
     * @return $this
     */
    public function mergeWith($otherArray)
    {
        // only call this after verifying isKeysSupersetOf
        $valueTypes = $this->valueTypes;
        $optionalKeys = $this->optionalKeys;
        foreach ($this->keyTypes as $i => $keyType) {
            $otherIndex = $otherArray->getKeyIndex($keyType);
            if ($otherIndex === null) {
                $optionalKeys[] = $i;
                continue;
            }
            if ($otherArray->isOptionalKey($otherIndex)) {
                $optionalKeys[] = $i;
            }
            $otherValueType = $otherArray->valueTypes[$otherIndex];
            $valueTypes[$i] = \PHPStan\Type\TypeCombinator::union($valueTypes[$i], $otherValueType);
        }
        $optionalKeys = \array_values(\array_unique($optionalKeys));
        return new self($this->keyTypes, $valueTypes, $this->nextAutoIndex, $optionalKeys);
    }
    /**
     * @param ConstantIntegerType|ConstantStringType $otherKeyType
     * @return int|null
     */
    private function getKeyIndex($otherKeyType)
    {
        foreach ($this->keyTypes as $i => $keyType) {
            if ($keyType->equals($otherKeyType)) {
                return $i;
            }
        }
        return null;
    }
    /**
     * @return $this
     */
    public function makeOffsetRequired(\PHPStan\Type\Type $offsetType)
    {
        $offsetType = \PHPStan\Type\ArrayType::castToArrayKeyType($offsetType);
        $optionalKeys = $this->optionalKeys;
        foreach ($this->keyTypes as $i => $keyType) {
            if (!$keyType->equals($offsetType)) {
                continue;
            }
            foreach ($optionalKeys as $j => $key) {
                if ($i === $key) {
                    unset($optionalKeys[$j]);
                    return new self($this->keyTypes, $this->valueTypes, $this->nextAutoIndex, \array_values($optionalKeys));
                }
            }
            break;
        }
        return $this;
    }
    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['keyTypes'], $properties['valueTypes'], $properties['nextAutoIndex'], $properties['optionalKeys'] ?? []);
    }
}
