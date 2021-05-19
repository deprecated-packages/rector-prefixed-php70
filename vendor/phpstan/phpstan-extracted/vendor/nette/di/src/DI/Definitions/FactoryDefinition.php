<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection;
/**
 * Definition of standard service.
 */
final class FactoryDefinition extends \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
{
    const METHOD_CREATE = 'create';
    /** @var array */
    public $parameters = [];
    /** @var Definition */
    private $resultDefinition;
    public function __construct()
    {
        $this->resultDefinition = new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition();
    }
    /** @return static */
    public function setImplement(string $type)
    {
        if (!\interface_exists($type)) {
            throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Service '{$this->getName()}': Interface '{$type}' not found.");
        }
        $rc = new \ReflectionClass($type);
        $method = $rc->getMethods()[0] ?? null;
        if (!$method || $method->isStatic() || $method->name !== self::METHOD_CREATE || \count($rc->getMethods()) > 1) {
            throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Service '{$this->getName()}': Interface {$type} must have just one non-static method create().");
        }
        return parent::setType($type);
    }
    /**
     * @return string|null
     */
    public function getImplement()
    {
        return $this->getType();
    }
    /**
     * @return string|null
     */
    public final function getResultType()
    {
        return $this->resultDefinition->getType();
    }
    /** @return static */
    public function setResultDefinition(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition $definition)
    {
        $this->resultDefinition = $definition;
        return $this;
    }
    /** @return ServiceDefinition */
    public function getResultDefinition() : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
    {
        return $this->resultDefinition;
    }
    /**
     * @deprecated use ->getResultDefinition()->setFactory()
     * @return static
     */
    public function setFactory($factory, array $args = [])
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->setFactory()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        $this->resultDefinition->setFactory($factory, $args);
        return $this;
    }
    /** @deprecated use ->getResultDefinition()->getFactory()
     * @return \_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement|null */
    public function getFactory()
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->getFactory()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        return $this->resultDefinition->getFactory();
    }
    /**
     * @deprecated use ->getResultDefinition()->getEntity()
     * @return mixed
     */
    public function getEntity()
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->getEntity()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        return $this->resultDefinition->getEntity();
    }
    /**
     * @deprecated use ->getResultDefinition()->setArguments()
     * @return static
     */
    public function setArguments(array $args = [])
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->setArguments()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        $this->resultDefinition->setArguments($args);
        return $this;
    }
    /**
     * @deprecated use ->getResultDefinition()->setSetup()
     * @return static
     */
    public function setSetup(array $setup)
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->setSetup()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        $this->resultDefinition->setSetup($setup);
        return $this;
    }
    /** @deprecated use ->getResultDefinition()->getSetup() */
    public function getSetup() : array
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->getSetup()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        return $this->resultDefinition->getSetup();
    }
    /**
     * @deprecated use ->getResultDefinition()->addSetup()
     * @return static
     */
    public function addSetup($entity, array $args = [])
    {
        \trigger_error(\sprintf('Service %s: %s() is deprecated, use ->getResultDefinition()->addSetup()', $this->getName(), __METHOD__), \E_USER_DEPRECATED);
        $this->resultDefinition->addSetup($entity, $args);
        return $this;
    }
    /** @return static */
    public function setParameters(array $params)
    {
        $this->parameters = $params;
        return $this;
    }
    public function getParameters() : array
    {
        return $this->parameters;
    }
    /**
     * @return void
     */
    public function resolveType(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        $resultDef = $this->resultDefinition;
        try {
            $resolver->resolveDefinition($resultDef);
            return;
        } catch (\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException $e) {
        }
        if (!$resultDef->getType()) {
            $interface = $this->getType();
            if (!$interface) {
                throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException('Type is missing in definition of service.');
            }
            $method = new \ReflectionMethod($interface, self::METHOD_CREATE);
            $returnType = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::getReturnType($method);
            if (!$returnType) {
                throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Method {$interface}::create() has not return type hint or annotation @return.");
            } elseif (!\class_exists($returnType) && !\interface_exists($returnType)) {
                throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Check a type hint or annotation @return of the {$interface}::create() method, class '{$returnType}' cannot be found.");
            }
            $resultDef->setType($returnType);
        }
        $resolver->resolveDefinition($resultDef);
    }
    /**
     * @return void
     */
    public function complete(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        $resultDef = $this->resultDefinition;
        if ($resultDef instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\ServiceDefinition) {
            if (!$this->parameters) {
                $this->completeParameters($resolver);
            }
            if ($resultDef->getEntity() instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference && !$resultDef->getFactory()->arguments) {
                $resultDef->setFactory([
                    // render as $container->createMethod()
                    new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerBuilder::THIS_CONTAINER),
                    \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Container::getMethodName($resultDef->getEntity()->getValue()),
                ]);
            }
        }
        $resolver->completeDefinition($resultDef);
    }
    /**
     * @return void
     */
    private function completeParameters(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        $interface = $this->getType();
        $method = new \ReflectionMethod($interface, self::METHOD_CREATE);
        $ctorParams = [];
        if (($class = $resolver->resolveEntityType($this->resultDefinition->getFactory())) && ($ctor = (new \ReflectionClass($class))->getConstructor())) {
            foreach ($ctor->getParameters() as $param) {
                $ctorParams[$param->name] = $param;
            }
        }
        foreach ($method->getParameters() as $param) {
            $hint = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getParameterType($param);
            if (isset($ctorParams[$param->name])) {
                $arg = $ctorParams[$param->name];
                $argHint = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getParameterType($arg);
                if ($hint !== $argHint && !\is_a($hint, (string) $argHint, \true)) {
                    throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Type hint for \${$param->name} in {$interface}::create() doesn't match type hint in {$class} constructor.");
                }
                $this->resultDefinition->getFactory()->arguments[$arg->getPosition()] = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ContainerBuilder::literal('$' . $arg->name);
            } elseif (!$this->resultDefinition->getSetup()) {
                $hint = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Helpers::getSuggestion(\array_keys($ctorParams), $param->name);
                throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Unused parameter \${$param->name} when implementing method {$interface}::create()" . ($hint ? ", did you mean \${$hint}?" : '.'));
            }
            $nullable = $hint && $param->allowsNull() && (!$param->isDefaultValueAvailable() || $param->getDefaultValue() !== null);
            $paramDef = ($nullable ? '?' : '') . $hint . ' ' . $param->name;
            if ($param->isDefaultValueAvailable()) {
                $this->parameters[$paramDef] = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getParameterDefaultValue($param);
            } else {
                $this->parameters[] = $paramDef;
            }
        }
    }
    /**
     * @return void
     */
    public function generateMethod(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method $method, \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator $generator)
    {
        $class = (new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType())->addImplement($this->getType());
        $class->addProperty('container')->setPrivate();
        $class->addMethod('__construct')->addBody('$this->container = $container;')->addParameter('container')->setType($generator->getClassName());
        $methodCreate = $class->addMethod(self::METHOD_CREATE);
        $this->resultDefinition->generateMethod($methodCreate, $generator);
        $body = $methodCreate->getBody();
        $body = \str_replace('$this', '$this->container', $body);
        $body = \str_replace('$this->container->container', '$this->container', $body);
        $rm = new \ReflectionMethod($this->getType(), self::METHOD_CREATE);
        $methodCreate->setParameters($generator->convertParameters($this->parameters))->setReturnType(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getReturnType($rm) ?: $this->getResultType())->setBody($body);
        $method->setBody('return new class ($this) ' . $class . ';');
    }
    public function __clone()
    {
        parent::__clone();
        $this->resultDefinition = \unserialize(\serialize($this->resultDefinition));
    }
}
