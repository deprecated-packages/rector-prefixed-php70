<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Config;

use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect;
use RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema;
/**
 * Service configuration schema.
 */
class DefinitionSchema implements \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
{
    use Nette\SmartObject;
    /** @var Nette\DI\ContainerBuilder */
    private $builder;
    public function __construct(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerBuilder $builder)
    {
        $this->builder = $builder;
    }
    public function complete($def, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        if ($def === [\false]) {
            return (object) $def;
        }
        if (\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Helpers::takeParent($def)) {
            $def['reset']['all'] = \true;
        }
        foreach (['arguments', 'setup', 'tags'] as $k) {
            if (isset($def[$k]) && \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Helpers::takeParent($def[$k])) {
                $def['reset'][$k] = \true;
            }
        }
        $def = $this->expandParameters($def);
        $type = $this->sniffType(\end($context->path), $def);
        $def = $this->getSchema($type)->complete($def, $context);
        if ($def) {
            $def->defType = $type;
        }
        return $def;
    }
    public function merge($def, $base)
    {
        if (!empty($def['alteration'])) {
            unset($def['alteration']);
        }
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::merge($def, $base);
    }
    /**
     * Normalizes configuration of service definitions.
     */
    public function normalize($def, \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        if ($def === null || $def === \false) {
            return (array) $def;
        } elseif (\is_string($def) && \interface_exists($def)) {
            return ['implement' => $def];
        } elseif ($def instanceof \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement && \is_string($def->getEntity()) && \interface_exists($def->getEntity())) {
            $res = ['implement' => $def->getEntity()];
            if (\array_keys($def->arguments) === ['tagged']) {
                $res += $def->arguments;
            } elseif (\count($def->arguments) > 1) {
                $res['references'] = $def->arguments;
            } elseif ($factory = \array_shift($def->arguments)) {
                $res['factory'] = $factory;
            }
            return $res;
        } elseif (!\is_array($def) || isset($def[0], $def[1])) {
            return ['factory' => $def];
        } elseif (\is_array($def)) {
            if (isset($def['class']) && !isset($def['type'])) {
                if ($def['class'] instanceof \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement) {
                    $key = \end($context->path);
                    \trigger_error("Service '{$key}': option 'class' should be changed to 'factory'.", \E_USER_DEPRECATED);
                    $def['factory'] = $def['class'];
                    unset($def['class']);
                } elseif (!isset($def['factory']) && !isset($def['dynamic']) && !isset($def['imported'])) {
                    $def['factory'] = $def['class'];
                    unset($def['class']);
                }
            }
            foreach (['class' => 'type', 'dynamic' => 'imported'] as $alias => $original) {
                if (\array_key_exists($alias, $def)) {
                    if (\array_key_exists($original, $def)) {
                        throw new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\InvalidConfigurationException("Options '{$alias}' and '{$original}' are aliases, use only '{$original}'.");
                    }
                    $def[$original] = $def[$alias];
                    unset($def[$alias]);
                }
            }
            return $def;
        } else {
            throw new \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\InvalidConfigurationException('Unexpected format of service definition');
        }
    }
    public function completeDefault(\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
    }
    private function sniffType($key, array $def) : string
    {
        if (\is_string($key)) {
            $name = \preg_match('#^@[\\w\\\\]+$#D', $key) ? $this->builder->getByType(\substr($key, 1), \false) : $key;
            if ($name && $this->builder->hasDefinition($name)) {
                return \get_class($this->builder->getDefinition($name));
            }
        }
        if (isset($def['implement'], $def['references']) || isset($def['implement'], $def['tagged'])) {
            return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\LocatorDefinition::class;
        } elseif (isset($def['implement'])) {
            return \method_exists($def['implement'], 'create') ? \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition::class : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\AccessorDefinition::class;
        } elseif (isset($def['imported'])) {
            return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ImportedDefinition::class;
        } else {
            return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition::class;
        }
    }
    private function expandParameters(array $config) : array
    {
        $params = $this->builder->parameters;
        if (isset($config['parameters'])) {
            foreach ((array) $config['parameters'] as $k => $v) {
                $v = \explode(' ', \is_int($k) ? $v : $k);
                $params[\end($v)] = $this->builder::literal('$' . \end($v));
            }
        }
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::expand($config, $params);
    }
    private static function getSchema(string $type) : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        static $cache;
        $cache = $cache ?: [\RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition::class => self::getServiceSchema(), \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\AccessorDefinition::class => self::getAccessorSchema(), \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition::class => self::getFactorySchema(), \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\LocatorDefinition::class => self::getLocatorSchema(), \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ImportedDefinition::class => self::getImportedSchema()];
        return $cache[$type];
    }
    private static function getServiceSchema() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['type' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('string'), 'factory' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('RectorPrefix20210518\\callable|_HumbugBox0b2f2d5c77b8\\Nette\\DI\\Definitions\\Statement'), 'arguments' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'setup' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::listOf('callable|_HumbugBox0b2f2d5c77b8\\Nette\\DI\\Definitions\\Statement|array:1'), 'inject' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::bool(), 'autowired' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('bool|string|array'), 'tags' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'reset' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'alteration' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::bool()]);
    }
    private static function getAccessorSchema() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['type' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'implement' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'factory' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('RectorPrefix20210518\\callable|_HumbugBox0b2f2d5c77b8\\Nette\\DI\\Definitions\\Statement'), 'autowired' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('bool|string|array'), 'tags' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array()]);
    }
    private static function getFactorySchema() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['type' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'factory' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('RectorPrefix20210518\\callable|_HumbugBox0b2f2d5c77b8\\Nette\\DI\\Definitions\\Statement'), 'implement' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'arguments' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'setup' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::listOf('callable|_HumbugBox0b2f2d5c77b8\\Nette\\DI\\Definitions\\Statement|array:1'), 'parameters' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'references' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'tagged' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'inject' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::bool(), 'autowired' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('bool|string|array'), 'tags' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'reset' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array()]);
    }
    private static function getLocatorSchema() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['implement' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'references' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'tagged' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'autowired' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('bool|string|array'), 'tags' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array()]);
    }
    private static function getImportedSchema() : \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['type' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::string(), 'imported' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::bool(), 'autowired' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::type('bool|string|array'), 'tags' => \RectorPrefix20210518\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array()]);
    }
}
