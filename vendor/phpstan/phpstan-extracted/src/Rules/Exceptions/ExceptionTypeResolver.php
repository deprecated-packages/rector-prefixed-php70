<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
use PHPStan\Reflection\ReflectionProvider;
class ExceptionTypeResolver
{
    /** @var ReflectionProvider */
    private $reflectionProvider;
    /** @var string[] */
    private $uncheckedExceptionRegexes;
    /** @var string[] */
    private $uncheckedExceptionClasses;
    /**
     * @param ReflectionProvider $reflectionProvider
     * @param string[] $uncheckedExceptionRegexes
     * @param string[] $uncheckedExceptionClasses
     */
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, array $uncheckedExceptionRegexes, array $uncheckedExceptionClasses)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->uncheckedExceptionRegexes = $uncheckedExceptionRegexes;
        $this->uncheckedExceptionClasses = $uncheckedExceptionClasses;
    }
    public function isCheckedException(string $className) : bool
    {
        foreach ($this->uncheckedExceptionRegexes as $regex) {
            if (\RectorPrefix20210517\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::match($className, $regex) !== null) {
                return \false;
            }
        }
        foreach ($this->uncheckedExceptionClasses as $uncheckedExceptionClass) {
            if ($className === $uncheckedExceptionClass) {
                return \false;
            }
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        foreach ($this->uncheckedExceptionClasses as $uncheckedExceptionClass) {
            if ($classReflection->getName() === $uncheckedExceptionClass) {
                return \false;
            }
            if (!$classReflection->isSubclassOf($uncheckedExceptionClass)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
