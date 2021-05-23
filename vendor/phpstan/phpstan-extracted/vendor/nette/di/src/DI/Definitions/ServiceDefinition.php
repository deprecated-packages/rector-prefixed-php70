<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException;
use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers as PhpHelpers;
/**
 * Definition of standard service.
 *
 * @property string|null $class
 * @property Statement $factory
 * @property Statement[] $setup
 */
final class ServiceDefinition extends \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
{
    /** @var Statement */
    private $factory;
    /** @var Statement[] */
    private $setup = [];
    public function __construct()
    {
        $this->factory = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement(null);
    }
    /** @deprecated Use setType()
     * @param string|null $type */
    public function setClass($type)
    {
        $this->setType($type);
        if (\func_num_args() > 1) {
            \trigger_error(\sprintf('Service %s: %s() second parameter $args is deprecated, use setFactory()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
            if ($args = \func_get_arg(1)) {
                $this->setFactory($type, $args);
            }
        }
        return $this;
    }
    /** @return static
     * @param string|null $type */
    public function setType($type)
    {
        return parent::setType($type);
    }
    /**
     * @param  string|array|Definition|Reference|Statement  $factory
     * @return static
     */
    public function setFactory($factory, array $args = [])
    {
        $this->factory = $factory instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement ? $factory : new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement($factory, $args);
        return $this;
    }
    public function getFactory() : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement
    {
        return $this->factory;
    }
    /** @return string|array|Definition|Reference|null */
    public function getEntity()
    {
        return $this->factory->getEntity();
    }
    /** @return static */
    public function setArguments(array $args = [])
    {
        $this->factory->arguments = $args;
        return $this;
    }
    /** @return static */
    public function setArgument($key, $value)
    {
        $this->factory->arguments[$key] = $value;
        return $this;
    }
    /**
     * @param  Statement[]  $setup
     * @return static
     */
    public function setSetup(array $setup)
    {
        foreach ($setup as $v) {
            if (!$v instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be Nette\\DI\\Definitions\\Statement[].');
            }
        }
        $this->setup = $setup;
        return $this;
    }
    /** @return Statement[] */
    public function getSetup() : array
    {
        return $this->setup;
    }
    /**
     * @param  string|array|Definition|Reference|Statement  $entity
     * @return static
     */
    public function addSetup($entity, array $args = [])
    {
        $this->setup[] = $entity instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement ? $entity : new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement($entity, $args);
        return $this;
    }
    /** @deprecated */
    public function setParameters(array $params)
    {
        throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DeprecatedException(\sprintf('Service %s: %s() is deprecated.', $this->getName(), __METHOD__));
    }
    /** @deprecated */
    public function getParameters() : array
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated.', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        return [];
    }
    /** @deprecated use $builder->addImportedDefinition(...)
     * @return void */
    public function setDynamic()
    {
        throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DeprecatedException(\sprintf('Service %s: %s() is deprecated, use $builder->addImportedDefinition(...)', $this->getName(), __METHOD__));
    }
    /** @deprecated use $builder->addFactoryDefinition(...) or addAccessorDefinition(...)
     * @return void */
    public function setImplement()
    {
        throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DeprecatedException(\sprintf('Service %s: %s() is deprecated, use $builder->addFactoryDefinition(...)', $this->getName(), __METHOD__));
    }
    /** @deprecated use addTag('nette.inject') */
    public function setInject(bool $state = \true)
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use addTag(Nette\\DI\\Extensions\\InjectExtension::TAG_INJECT)', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        return $this->addTag(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions\InjectExtension::TAG_INJECT, $state);
    }
    /**
     * @return void
     */
    public function resolveType(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        if (!$this->getEntity()) {
            if (!$this->getType()) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException('Factory and type are missing in definition of service.');
            }
            $this->setFactory($this->getType(), $this->factory->arguments ?? []);
        } elseif (!$this->getType()) {
            $type = $resolver->resolveEntityType($this->factory);
            if (!$type) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException('Unknown service type, specify it or declare return type of factory.');
            }
            $this->setType($type);
            $resolver->addDependency(new \ReflectionClass($type));
        }
        // auto-disable autowiring for aliases
        if ($this->getAutowired() === \true && $this->getEntity() instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference) {
            $this->setAutowired(\false);
        }
    }
    /**
     * @return void
     */
    public function complete(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        $entity = $this->factory->getEntity();
        if ($entity instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference && !$this->factory->arguments && !$this->setup) {
            $ref = $resolver->normalizeReference($entity);
            $this->setFactory([new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerBuilder::THIS_CONTAINER), 'getService'], [$ref->getValue()]);
        }
        $this->factory = $resolver->completeStatement($this->factory);
        foreach ($this->setup as &$setup) {
            if (\is_string($setup->getEntity()) && \strpbrk($setup->getEntity(), ':@?\\') === \false) {
                // auto-prepend @self
                $setup = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement([new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference::SELF), $setup->getEntity()], $setup->arguments);
            }
            $setup = $resolver->completeStatement($setup, \true);
        }
    }
    /**
     * @return void
     */
    public function generateMethod(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method $method, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator $generator)
    {
        $entity = $this->factory->getEntity();
        $code = $generator->formatStatement($this->factory) . ";\n";
        if (!$this->setup) {
            $method->setBody('return ' . $code);
            return;
        }
        $code = '$service = ' . $code;
        $type = $this->getType();
        if ($type !== $entity && !(\is_array($entity) && $entity[0] instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference && $entity[0]->getValue() === \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerBuilder::THIS_CONTAINER) && !(\is_string($entity) && \preg_match('#^[\\w\\\\]+$#D', $entity) && \is_subclass_of($entity, $type))) {
            $code .= \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::formatArgs("if (!\$service instanceof {$type}) {\n" . "\tthrow new Nette\\UnexpectedValueException(?);\n}\n", ["Unable to create service '{$this->getName()}', value returned by factory is not {$type} type."]);
        }
        foreach ($this->setup as $setup) {
            $code .= $generator->formatStatement($setup) . ";\n";
        }
        $code .= 'return $service;';
        $method->setBody($code);
    }
    public function __clone()
    {
        parent::__clone();
        $this->factory = \unserialize(\serialize($this->factory));
        $this->setup = \unserialize(\serialize($this->setup));
    }
}
\class_exists(\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceDefinition::class);
