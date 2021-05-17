<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Configuration\Option;
use RectorPrefix20210517\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20210517\Symfony\Component\DependencyInjection\ContainerBuilder;
final class DeprecationWarningCompilerPass implements \RectorPrefix20210517\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var array<string, string>
     */
    const DEPRECATED_PARAMETERS = [\Rector\Core\Configuration\Option::SETS => 'Use $containerConfigurator->import(<set>); instead'];
    /**
     * @return void
     */
    public function process(\RectorPrefix20210517\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $parametersBag = $containerBuilder->getParameterBag();
        foreach (self::DEPRECATED_PARAMETERS as $parameter => $message) {
            if (!$parametersBag->has($parameter)) {
                continue;
            }
            $setsParameters = $parametersBag->get($parameter);
            if ($setsParameters === []) {
                continue;
            }
            $message = \sprintf('The "%s" parameter is deprecated. %s', $parameter, $message);
            \trigger_error($message);
            // to make it noticable
            \sleep(2);
        }
    }
}
