<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
interface LoaderMethodInterface
{
    /**
     * @return void
     */
    public function __invoke(\PHPStan\BetterReflection\Reflection\ReflectionClass $classInfo);
}
