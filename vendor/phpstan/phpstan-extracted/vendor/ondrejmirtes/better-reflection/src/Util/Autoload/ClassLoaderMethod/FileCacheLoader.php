<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod\Exception\SignatureCheckFailed;
use PHPStan\BetterReflection\Util\Autoload\ClassPrinter\ClassPrinterInterface;
use PHPStan\BetterReflection\Util\Autoload\ClassPrinter\PhpParserPrinter;
use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\CheckerInterface;
use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\Sha1SumEncoder;
use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\FileContentChecker;
use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\FileContentSigner;
use RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\SignerInterface;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sha1;
use function str_replace;
final class FileCacheLoader implements \PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod\LoaderMethodInterface
{
    /** @var string */
    private $cacheDirectory;
    /** @var ClassPrinterInterface */
    private $classPrinter;
    /** @var SignerInterface */
    private $signer;
    /** @var CheckerInterface */
    private $checker;
    public function __construct(string $cacheDirectory, \PHPStan\BetterReflection\Util\Autoload\ClassPrinter\ClassPrinterInterface $classPrinter, \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\SignerInterface $signer, \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\CheckerInterface $checker)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->classPrinter = $classPrinter;
        $this->signer = $signer;
        $this->checker = $checker;
    }
    /**
     * {@inheritdoc}
     *
     * @throws SignatureCheckFailed
     * @return void
     */
    public function __invoke(\PHPStan\BetterReflection\Reflection\ReflectionClass $classInfo)
    {
        $filename = $this->cacheDirectory . '/' . \sha1($classInfo->getName());
        if (!\file_exists($filename)) {
            $code = "<?php\n" . $this->classPrinter->__invoke($classInfo);
            \file_put_contents($filename, \str_replace('<?php', "<?php\n// " . $this->signer->sign($code), $code));
        }
        if (!$this->checker->check(\file_get_contents($filename))) {
            throw \PHPStan\BetterReflection\Util\Autoload\ClassLoaderMethod\Exception\SignatureCheckFailed::fromReflectionClass($classInfo);
        }
        /** @noinspection PhpIncludeInspection */
        require_once $filename;
    }
    /**
     * @return $this
     */
    public static function defaultFileCacheLoader(string $cacheDirectory)
    {
        return new self($cacheDirectory, new \PHPStan\BetterReflection\Util\Autoload\ClassPrinter\PhpParserPrinter(), new \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\FileContentSigner(new \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\Sha1SumEncoder()), new \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\FileContentChecker(new \RectorPrefix20210527\_HumbugBox0b2f2d5c77b8\Roave\Signature\Encoder\Sha1SumEncoder()));
    }
}
