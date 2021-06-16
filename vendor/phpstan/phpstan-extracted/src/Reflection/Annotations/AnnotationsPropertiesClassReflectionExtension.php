<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Generic\TemplateTypeHelper;
class AnnotationsPropertiesClassReflectionExtension implements \PHPStan\Reflection\PropertiesClassReflectionExtension
{
    /** @var PropertyReflection[][] */
    private $properties = [];
    public function hasProperty(\PHPStan\Reflection\ClassReflection $classReflection, string $propertyName) : bool
    {
        if (!isset($this->properties[$classReflection->getCacheKey()][$propertyName])) {
            $property = $this->findClassReflectionWithProperty($classReflection, $classReflection, $propertyName);
            if ($property === null) {
                return \false;
            }
            $this->properties[$classReflection->getCacheKey()][$propertyName] = $property;
        }
        return isset($this->properties[$classReflection->getCacheKey()][$propertyName]);
    }
    public function getProperty(\PHPStan\Reflection\ClassReflection $classReflection, string $propertyName) : \PHPStan\Reflection\PropertyReflection
    {
        return $this->properties[$classReflection->getCacheKey()][$propertyName];
    }
    /**
     * @return \PHPStan\Reflection\PropertyReflection|null
     */
    private function findClassReflectionWithProperty(\PHPStan\Reflection\ClassReflection $classReflection, \PHPStan\Reflection\ClassReflection $declaringClass, string $propertyName)
    {
        $propertyTags = $classReflection->getPropertyTags();
        if (isset($propertyTags[$propertyName])) {
            return new \PHPStan\Reflection\Annotations\AnnotationPropertyReflection($declaringClass, \PHPStan\Type\Generic\TemplateTypeHelper::resolveTemplateTypes($propertyTags[$propertyName]->getType(), $classReflection->getActiveTemplateTypeMap()), $propertyTags[$propertyName]->isReadable(), $propertyTags[$propertyName]->isWritable());
        }
        foreach ($classReflection->getTraits() as $traitClass) {
            $methodWithDeclaringClass = $this->findClassReflectionWithProperty($traitClass, $classReflection, $propertyName);
            if ($methodWithDeclaringClass === null) {
                continue;
            }
            return $methodWithDeclaringClass;
        }
        foreach ($classReflection->getParents() as $parentClass) {
            $methodWithDeclaringClass = $this->findClassReflectionWithProperty($parentClass, $parentClass, $propertyName);
            if ($methodWithDeclaringClass === null) {
                foreach ($parentClass->getTraits() as $traitClass) {
                    $parentTraitMethodWithDeclaringClass = $this->findClassReflectionWithProperty($traitClass, $parentClass, $propertyName);
                    if ($parentTraitMethodWithDeclaringClass === null) {
                        continue;
                    }
                    return $parentTraitMethodWithDeclaringClass;
                }
                continue;
            }
            return $methodWithDeclaringClass;
        }
        foreach ($classReflection->getInterfaces() as $interfaceClass) {
            $methodWithDeclaringClass = $this->findClassReflectionWithProperty($interfaceClass, $interfaceClass, $propertyName);
            if ($methodWithDeclaringClass === null) {
                continue;
            }
            return $methodWithDeclaringClass;
        }
        return null;
    }
}
