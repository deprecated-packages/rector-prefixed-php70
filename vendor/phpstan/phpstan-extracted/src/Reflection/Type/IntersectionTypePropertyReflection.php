<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
class IntersectionTypePropertyReflection implements \PHPStan\Reflection\PropertyReflection
{
    /** @var PropertyReflection[] */
    private $properties;
    /**
     * @param \PHPStan\Reflection\PropertyReflection[] $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->properties[0]->getDeclaringClass();
    }
    public function isStatic() : bool
    {
        foreach ($this->properties as $property) {
            if ($property->isStatic()) {
                return \true;
            }
        }
        return \false;
    }
    public function isPrivate() : bool
    {
        foreach ($this->properties as $property) {
            if (!$property->isPrivate()) {
                return \false;
            }
        }
        return \true;
    }
    public function isPublic() : bool
    {
        foreach ($this->properties as $property) {
            if ($property->isPublic()) {
                return \true;
            }
        }
        return \false;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\PropertyReflection $property) : TrinaryLogic {
            return $property->isDeprecated();
        }, $this->properties));
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        $descriptions = [];
        foreach ($this->properties as $property) {
            if (!$property->isDeprecated()->yes()) {
                continue;
            }
            $description = $property->getDeprecatedDescription();
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
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::maxMin(...\array_map(static function (\PHPStan\Reflection\PropertyReflection $property) : TrinaryLogic {
            return $property->isInternal();
        }, $this->properties));
    }
    /**
     * @return string|null
     */
    public function getDocComment()
    {
        return null;
    }
    public function getReadableType() : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeCombinator::intersect(...\array_map(static function (\PHPStan\Reflection\PropertyReflection $property) : Type {
            return $property->getReadableType();
        }, $this->properties));
    }
    public function getWritableType() : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeCombinator::intersect(...\array_map(static function (\PHPStan\Reflection\PropertyReflection $property) : Type {
            return $property->getWritableType();
        }, $this->properties));
    }
    public function canChangeTypeAfterAssignment() : bool
    {
        foreach ($this->properties as $property) {
            if (!$property->canChangeTypeAfterAssignment()) {
                return \false;
            }
        }
        return \true;
    }
    public function isReadable() : bool
    {
        foreach ($this->properties as $property) {
            if (!$property->isReadable()) {
                return \false;
            }
        }
        return \true;
    }
    public function isWritable() : bool
    {
        foreach ($this->properties as $property) {
            if (!$property->isWritable()) {
                return \false;
            }
        }
        return \true;
    }
}
