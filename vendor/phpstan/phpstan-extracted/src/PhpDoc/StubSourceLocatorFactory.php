<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\BetterReflection\Reflector\FunctionReflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository;
class StubSourceLocatorFactory
{
    /** @var \PhpParser\Parser */
    private $parser;
    /** @var PhpStormStubsSourceStubber */
    private $phpStormStubsSourceStubber;
    /** @var \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository */
    private $optimizedSingleFileSourceLocatorRepository;
    /** @var \PHPStan\DependencyInjection\Container */
    private $container;
    /** @var string[] */
    private $stubFiles;
    /**
     * @param string[] $stubFiles
     */
    public function __construct(\PhpParser\Parser $parser, \PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber $phpStormStubsSourceStubber, \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository $optimizedSingleFileSourceLocatorRepository, \PHPStan\DependencyInjection\Container $container, array $stubFiles)
    {
        $this->parser = $parser;
        $this->phpStormStubsSourceStubber = $phpStormStubsSourceStubber;
        $this->optimizedSingleFileSourceLocatorRepository = $optimizedSingleFileSourceLocatorRepository;
        $this->container = $container;
        $this->stubFiles = $stubFiles;
    }
    public function create() : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
    {
        $locators = [];
        $astLocator = new \PHPStan\BetterReflection\SourceLocator\Ast\Locator($this->parser, function () : FunctionReflector {
            return $this->container->getService('stubFunctionReflector');
        });
        foreach ($this->stubFiles as $stubFile) {
            $locators[] = $this->optimizedSingleFileSourceLocatorRepository->getOrCreate($stubFile);
        }
        $locators[] = new \PHPStan\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator($astLocator, $this->phpStormStubsSourceStubber);
        return new \PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator(new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator($locators));
    }
}
