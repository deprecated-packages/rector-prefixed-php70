<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers;
/**
 * Service definitions loader.
 */
final class ServicesExtension extends \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\CompilerExtension
{
    use Nette\SmartObject;
    public function getConfigSchema() : \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::arrayOf(new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\DefinitionSchema($this->getContainerBuilder()));
    }
    public function loadConfiguration()
    {
        $this->loadDefinitions($this->config);
    }
    /**
     * Loads list of service definitions.
     */
    public function loadDefinitions(array $config)
    {
        foreach ($config as $key => $defConfig) {
            $this->loadDefinition($this->convertKeyToName($key), $defConfig);
        }
    }
    /**
     * Loads service definition from normalized configuration.
     * @param string|null $name
     * @return void
     */
    private function loadDefinition($name, \stdClass $config)
    {
        try {
            if ((array) $config === [\false]) {
                $this->getContainerBuilder()->removeDefinition($name);
                return;
            } elseif (!empty($config->alteration) && !$this->getContainerBuilder()->hasDefinition($name)) {
                throw new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\InvalidConfigurationException('missing original definition for alteration.');
            }
            $def = $this->retrieveDefinition($name, $config);
            static $methods = [\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition::class => 'updateServiceDefinition', \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\AccessorDefinition::class => 'updateAccessorDefinition', \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition::class => 'updateFactoryDefinition', \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\LocatorDefinition::class => 'updateLocatorDefinition', \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ImportedDefinition::class => 'updateImportedDefinition'];
            $this->{$methods[$config->defType]}($def, $config);
            $this->updateDefinition($def, $config);
        } catch (\Exception $e) {
            throw new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\InvalidConfigurationException(($name ? "Service '{$name}': " : '') . $e->getMessage(), 0, $e);
        }
    }
    /**
     * Updates service definition according to normalized configuration.
     * @return void
     */
    private function updateServiceDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition $definition, \stdClass $config)
    {
        if ($config->factory) {
            $definition->setFactory(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments([$config->factory])[0]);
            $definition->setType(null);
        }
        if ($config->type) {
            $definition->setType($config->type);
        }
        if ($config->arguments) {
            $arguments = \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($config->arguments);
            if (empty($config->reset['arguments']) && !\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Utils\Arrays::isList($arguments)) {
                $arguments += $definition->getFactory()->arguments;
            }
            $definition->setArguments($arguments);
        }
        if (isset($config->setup)) {
            if (!empty($config->reset['setup'])) {
                $definition->setSetup([]);
            }
            foreach (\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($config->setup) as $id => $setup) {
                if (\is_array($setup)) {
                    $setup = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement(\key($setup), \array_values($setup));
                }
                $definition->addSetup($setup);
            }
        }
        if (isset($config->inject)) {
            $definition->addTag(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions\InjectExtension::TAG_INJECT, $config->inject);
        }
    }
    /**
     * @return void
     */
    private function updateAccessorDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\AccessorDefinition $definition, \stdClass $config)
    {
        if (isset($config->implement)) {
            $definition->setImplement($config->implement);
        }
        if ($ref = $config->factory ?? $config->type ?? null) {
            $definition->setReference($ref);
        }
    }
    /**
     * @return void
     */
    private function updateFactoryDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition $definition, \stdClass $config)
    {
        $resultDef = $definition->getResultDefinition();
        if (isset($config->implement)) {
            $definition->setImplement($config->implement);
            $definition->setAutowired(\true);
        }
        if ($config->factory) {
            $resultDef->setFactory(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments([$config->factory])[0]);
        }
        if ($config->type) {
            $resultDef->setFactory($config->type);
        }
        if ($config->arguments) {
            $arguments = \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($config->arguments);
            if (empty($config->reset['arguments']) && !\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Utils\Arrays::isList($arguments)) {
                $arguments += $resultDef->getFactory()->arguments;
            }
            $resultDef->setArguments($arguments);
        }
        if (isset($config->setup)) {
            if (!empty($config->reset['setup'])) {
                $resultDef->setSetup([]);
            }
            foreach (\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($config->setup) as $id => $setup) {
                if (\is_array($setup)) {
                    $setup = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement(\key($setup), \array_values($setup));
                }
                $resultDef->addSetup($setup);
            }
        }
        if (isset($config->parameters)) {
            $definition->setParameters($config->parameters);
        }
        if (isset($config->inject)) {
            $definition->addTag(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions\InjectExtension::TAG_INJECT, $config->inject);
        }
    }
    /**
     * @return void
     */
    private function updateLocatorDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\LocatorDefinition $definition, \stdClass $config)
    {
        if (isset($config->implement)) {
            $definition->setImplement($config->implement);
        }
        if (isset($config->references)) {
            $definition->setReferences($config->references);
        }
        if (isset($config->tagged)) {
            $definition->setTagged($config->tagged);
        }
    }
    /**
     * @return void
     */
    private function updateImportedDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ImportedDefinition $definition, \stdClass $config)
    {
        if ($config->type) {
            $definition->setType($config->type);
        }
    }
    /**
     * @return void
     */
    private function updateDefinition(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition $definition, \stdClass $config)
    {
        if (isset($config->autowired)) {
            $definition->setAutowired($config->autowired);
        }
        if (isset($config->tags)) {
            if (!empty($config->reset['tags'])) {
                $definition->setTags([]);
            }
            foreach ($config->tags as $tag => $attrs) {
                if (\is_int($tag) && \is_string($attrs)) {
                    $definition->addTag($attrs);
                } else {
                    $definition->addTag($tag, $attrs);
                }
            }
        }
    }
    /**
     * @return string|null
     */
    private function convertKeyToName($key)
    {
        if (\is_int($key)) {
            return null;
        } elseif (\preg_match('#^@[\\w\\\\]+$#D', $key)) {
            return $this->getContainerBuilder()->getByType(\substr($key, 1), \true);
        }
        return $key;
    }
    /**
     * @param string|null $name
     */
    private function retrieveDefinition($name, \stdClass $config) : \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
    {
        $builder = $this->getContainerBuilder();
        if (!empty($config->reset['all'])) {
            $builder->removeDefinition($name);
        }
        return $name && $builder->hasDefinition($name) ? $builder->getDefinition($name) : $builder->addDefinition($name, new $config->defType());
    }
}
