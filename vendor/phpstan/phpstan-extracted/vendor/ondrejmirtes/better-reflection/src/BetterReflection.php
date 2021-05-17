<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\Reflector\ConstantReflector;
use PHPStan\BetterReflection\Reflector\FunctionReflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator as AstLocator;
use PHPStan\BetterReflection\SourceLocator\Ast\Parser\MemoizingParser;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\AggregateSourceStubber;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\BetterReflection\Util\FindReflectionOnLine;
use const PHP_VERSION_ID;
final class BetterReflection
{
    /** @var int */
    public static $phpVersion = \PHP_VERSION_ID;
    /** @var SourceLocator|null */
    private static $sharedSourceLocator;
    /** @var SourceLocator|null */
    private $sourceLocator;
    /** @var ClassReflector|null */
    private static $sharedClassReflector;
    /** @var ClassReflector|null */
    private $classReflector;
    /** @var FunctionReflector|null */
    private static $sharedFunctionReflector;
    /** @var FunctionReflector|null */
    private $functionReflector;
    /** @var ConstantReflector|null */
    private static $sharedConstantReflector;
    /** @var ConstantReflector|null */
    private $constantReflector;
    /** @var Parser|null */
    private static $sharedPhpParser;
    /** @var Parser|null */
    private $phpParser;
    /** @var SourceStubber|null */
    private static $sharedSourceStubber;
    /** @var SourceStubber|null */
    private $sourceStubber;
    /** @var AstLocator|null */
    private $astLocator;
    /** @var FindReflectionOnLine|null */
    private $findReflectionOnLine;
    /**
     * @return void
     */
    public static function populate(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator, \PHPStan\BetterReflection\Reflector\ClassReflector $classReflector, \PHPStan\BetterReflection\Reflector\FunctionReflector $functionReflector, \PHPStan\BetterReflection\Reflector\ConstantReflector $constantReflector, \PhpParser\Parser $phpParser, \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber $sourceStubber)
    {
        self::$sharedSourceLocator = $sourceLocator;
        self::$sharedClassReflector = $classReflector;
        self::$sharedFunctionReflector = $functionReflector;
        self::$sharedConstantReflector = $constantReflector;
        self::$sharedPhpParser = $phpParser;
        self::$sharedSourceStubber = $sourceStubber;
    }
    public function __construct()
    {
        $this->sourceLocator = self::$sharedSourceLocator;
        $this->classReflector = self::$sharedClassReflector;
        $this->functionReflector = self::$sharedFunctionReflector;
        $this->constantReflector = self::$sharedConstantReflector;
        $this->phpParser = self::$sharedPhpParser;
        $this->sourceStubber = self::$sharedSourceStubber;
    }
    public function sourceLocator() : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
    {
        $astLocator = $this->astLocator();
        $sourceStubber = $this->sourceStubber();
        return $this->sourceLocator ?? ($this->sourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator(new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator([new \PHPStan\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator($astLocator, $sourceStubber), new \PHPStan\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator($astLocator, $sourceStubber), new \PHPStan\BetterReflection\SourceLocator\Type\AutoloadSourceLocator($astLocator, $this->phpParser())])));
    }
    public function classReflector() : \PHPStan\BetterReflection\Reflector\ClassReflector
    {
        return $this->classReflector ?? ($this->classReflector = new \PHPStan\BetterReflection\Reflector\ClassReflector($this->sourceLocator()));
    }
    public function functionReflector() : \PHPStan\BetterReflection\Reflector\FunctionReflector
    {
        return $this->functionReflector ?? ($this->functionReflector = new \PHPStan\BetterReflection\Reflector\FunctionReflector($this->sourceLocator(), $this->classReflector()));
    }
    public function constantReflector() : \PHPStan\BetterReflection\Reflector\ConstantReflector
    {
        return $this->constantReflector ?? ($this->constantReflector = new \PHPStan\BetterReflection\Reflector\ConstantReflector($this->sourceLocator(), $this->classReflector()));
    }
    public function phpParser() : \PhpParser\Parser
    {
        return $this->phpParser ?? ($this->phpParser = new \PHPStan\BetterReflection\SourceLocator\Ast\Parser\MemoizingParser((new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7, new \PhpParser\Lexer\Emulative(['usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos']]))));
    }
    public function astLocator() : \PHPStan\BetterReflection\SourceLocator\Ast\Locator
    {
        return $this->astLocator ?? ($this->astLocator = new \PHPStan\BetterReflection\SourceLocator\Ast\Locator($this->phpParser(), function () : FunctionReflector {
            return $this->functionReflector();
        }));
    }
    public function findReflectionsOnLine() : \PHPStan\BetterReflection\Util\FindReflectionOnLine
    {
        return $this->findReflectionOnLine ?? ($this->findReflectionOnLine = new \PHPStan\BetterReflection\Util\FindReflectionOnLine($this->sourceLocator(), $this->astLocator()));
    }
    public function sourceStubber() : \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber
    {
        return $this->sourceStubber ?? ($this->sourceStubber = new \PHPStan\BetterReflection\SourceLocator\SourceStubber\AggregateSourceStubber(new \PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber($this->phpParser(), self::$phpVersion), new \PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber()));
    }
}
