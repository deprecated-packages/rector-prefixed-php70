<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\TrinaryLogic;
interface BuiltinMethodReflection
{
    public function getName() : string;
    /**
     * @return \ReflectionMethod|null
     */
    public function getReflection();
    /**
     * @return string|false
     */
    public function getFileName();
    public function getDeclaringClass() : \ReflectionClass;
    /**
     * @return int|false
     */
    public function getStartLine();
    /**
     * @return int|false
     */
    public function getEndLine();
    /**
     * @return string|null
     */
    public function getDocComment();
    public function isStatic() : bool;
    public function isPrivate() : bool;
    public function isPublic() : bool;
    /**
     * @return $this
     */
    public function getPrototype();
    public function isDeprecated() : \PHPStan\TrinaryLogic;
    public function isVariadic() : bool;
    /**
     * @return \ReflectionType|null
     */
    public function getReturnType();
    /**
     * @return \ReflectionParameter[]
     */
    public function getParameters() : array;
    public function isFinal() : bool;
    public function isInternal() : bool;
    public function isAbstract() : bool;
}
