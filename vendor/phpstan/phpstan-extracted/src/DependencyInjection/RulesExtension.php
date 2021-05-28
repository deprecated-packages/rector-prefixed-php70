<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect;
use PHPStan\Rules\RegistryFactory;
class RulesExtension extends \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\DI\CompilerExtension
{
    public function getConfigSchema() : \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::listOf('string');
    }
    /**
     * @return void
     */
    public function loadConfiguration()
    {
        /** @var mixed[] $config */
        $config = $this->config;
        $builder = $this->getContainerBuilder();
        foreach ($config as $key => $rule) {
            $builder->addDefinition($this->prefix((string) $key))->setFactory($rule)->setAutowired(\false)->addTag(\PHPStan\Rules\RegistryFactory::RULE_TAG);
        }
    }
}
