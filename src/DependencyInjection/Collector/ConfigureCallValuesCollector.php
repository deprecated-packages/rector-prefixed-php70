<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Collector;

use RectorPrefix20210620\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20210620\Symplify\PackageBuilder\Yaml\ParametersMerger;
final class ConfigureCallValuesCollector
{
    /**
     * @var mixed[]
     */
    private $configureCallValuesByRectorClass = [];
    /**
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    public function __construct()
    {
        $this->parametersMerger = new \RectorPrefix20210620\Symplify\PackageBuilder\Yaml\ParametersMerger();
    }
    /**
     * @return mixed[]
     */
    public function getConfigureCallValues(string $rectorClass) : array
    {
        return $this->configureCallValuesByRectorClass[$rectorClass] ?? [];
    }
    /**
     * @return void
     */
    public function collectFromServiceAndClassName(string $className, \RectorPrefix20210620\Symfony\Component\DependencyInjection\Definition $definition)
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] !== 'configure') {
                continue;
            }
            $this->addConfigureCallValues($className, $methodCall[1]);
        }
    }
    /**
     * @param mixed[] $configureValues
     * @return void
     */
    private function addConfigureCallValues(string $rectorClass, array $configureValues)
    {
        foreach ($configureValues as $configureValue) {
            if (!isset($this->configureCallValuesByRectorClass[$rectorClass])) {
                $this->configureCallValuesByRectorClass[$rectorClass] = $configureValue;
            } else {
                $mergedParameters = $this->parametersMerger->merge($this->configureCallValuesByRectorClass[$rectorClass], $configureValue);
                $this->configureCallValuesByRectorClass[$rectorClass] = $mergedParameters;
            }
        }
    }
}
