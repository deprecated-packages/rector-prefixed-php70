<?php

declare (strict_types=1);
namespace RectorPrefix20210620\Symplify\SymplifyKernel\Strings;

use RectorPrefix20210620\Nette\Utils\Strings;
use RectorPrefix20210620\Symplify\SymplifyKernel\Exception\HttpKernel\TooGenericKernelClassException;
use RectorPrefix20210620\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class KernelUniqueHasher
{
    /**
     * @var StringsConverter
     */
    private $stringsConverter;
    public function __construct()
    {
        $this->stringsConverter = new \RectorPrefix20210620\Symplify\SymplifyKernel\Strings\StringsConverter();
    }
    public function hashKernelClass(string $kernelClass) : string
    {
        $this->ensureIsNotGenericKernelClass($kernelClass);
        $shortClassName = (string) \RectorPrefix20210620\Nette\Utils\Strings::after($kernelClass, '\\', -1);
        $userSpecificShortClassName = $shortClassName . \get_current_user();
        return $this->stringsConverter->camelCaseToGlue($userSpecificShortClassName, '_');
    }
    /**
     * @return void
     */
    private function ensureIsNotGenericKernelClass(string $kernelClass)
    {
        if ($kernelClass !== \RectorPrefix20210620\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel::class) {
            return;
        }
        $message = \sprintf('Instead of "%s", provide final Kernel class', \RectorPrefix20210620\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel::class);
        throw new \RectorPrefix20210620\Symplify\SymplifyKernel\Exception\HttpKernel\TooGenericKernelClassException($message);
    }
}
