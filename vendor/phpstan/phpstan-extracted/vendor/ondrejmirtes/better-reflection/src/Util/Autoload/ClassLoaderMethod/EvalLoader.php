<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Util\Autoload\ClassPrinter\ClassPrinterInterface;
final class EvalLoader implements \PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod\LoaderMethodInterface
{
    /** @var ClassPrinterInterface */
    private $classPrinter;
    public function __construct(\PHPStan\BetterReflection\Util\Autoload\ClassPrinter\ClassPrinterInterface $classPrinter)
    {
        $this->classPrinter = $classPrinter;
    }
    /**
     * @return void
     */
    public function __invoke(\PHPStan\BetterReflection\Reflection\ReflectionClass $classInfo)
    {
        eval($this->classPrinter->__invoke($classInfo));
    }
}
