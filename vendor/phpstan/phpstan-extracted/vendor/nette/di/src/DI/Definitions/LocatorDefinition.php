<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection;
/**
 * Multi accessor/factory definition.
 */
final class LocatorDefinition extends \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
{
    /** @var Reference[] */
    private $references = [];
    /** @var string|null */
    private $tagged;
    /** @return static */
    public function setImplement(string $type)
    {
        if (!\interface_exists($type)) {
            throw new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException(\sprintf("Service '%s': Interface '%s' not found.", $this->getName(), $type));
        }
        $methods = (new \ReflectionClass($type))->getMethods();
        if (!$methods) {
            throw new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException(\sprintf("Service '%s': Interface %s must have at least one method.", $this->getName(), $type));
        }
        foreach ($methods as $method) {
            if ($method->isStatic() || !(\preg_match('#^(get|create)$#', $method->name) && $method->getNumberOfParameters() === 1 || \preg_match('#^(get|create)[A-Z]#', $method->name) && $method->getNumberOfParameters() === 0)) {
                throw new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException(\sprintf("Service '%s': Method %s::%s() does not meet the requirements: is create(\$name), get(\$name), create*() or get*() and is non-static.", $this->getName(), $type, $method->name));
            }
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
    /** @return static */
    public function setReferences(array $references)
    {
        $this->references = [];
        foreach ($references as $name => $ref) {
            $this->references[$name] = \substr($ref, 0, 1) === '@' ? new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\substr($ref, 1)) : \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference::fromType($ref);
        }
        return $this;
    }
    /** @return Reference[] */
    public function getReferences() : array
    {
        return $this->references;
    }
    /** @return static
     * @param string|null $tagged */
    public function setTagged($tagged)
    {
        $this->tagged = $tagged;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getTagged()
    {
        return $this->tagged;
    }
    /**
     * @return void
     */
    public function resolveType(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
    }
    /**
     * @return void
     */
    public function complete(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        if ($this->tagged !== null) {
            $this->references = [];
            foreach ($resolver->getContainerBuilder()->findByTag($this->tagged) as $name => $tag) {
                if (isset($this->references[$tag])) {
                    \trigger_error("Service '{$this->getName()}': duplicated tag '{$this->tagged}' with value '{$tag}'.", \E_USER_NOTICE);
                }
                $this->references[$tag] = new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference($name);
            }
        }
        foreach ($this->references as $name => $ref) {
            $this->references[$name] = $resolver->normalizeReference($ref);
        }
    }
    /**
     * @return void
     */
    public function generateMethod(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method $method, \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator $generator)
    {
        $class = (new \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType())->addImplement($this->getType());
        $class->addProperty('container')->setPrivate();
        $class->addMethod('__construct')->addBody('$this->container = $container;')->addParameter('container')->setType($generator->getClassName());
        foreach ((new \ReflectionClass($this->getType()))->getMethods() as $rm) {
            \preg_match('#^(get|create)(.*)#', $rm->name, $m);
            $name = \lcfirst($m[2]);
            $nullable = $rm->getReturnType()->allowsNull();
            $methodInner = $class->addMethod($rm->name)->setReturnType(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getReturnType($rm))->setReturnNullable($nullable);
            if (!$name) {
                $class->addProperty('mapping', \array_map(function ($item) {
                    return $item->getValue();
                }, $this->references))->setPrivate();
                $methodInner->setBody('if (!isset($this->mapping[$name])) {
	' . ($nullable ? 'return null;' : 'throw new Nette\\DI\\MissingServiceException("Service \'$name\' is not defined.");') . '
}
return $this->container->' . $m[1] . 'Service($this->mapping[$name]);')->addParameter('name');
            } elseif (isset($this->references[$name])) {
                $ref = $this->references[$name]->getValue();
                if ($m[1] === 'get') {
                    $methodInner->setBody('return $this->container->getService(?);', [$ref]);
                } else {
                    $methodInner->setBody('return $this->container->?();', [\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Nette\DI\Container::getMethodName($ref)]);
                }
            } else {
                $methodInner->setBody($nullable ? 'return null;' : 'throw new Nette\\DI\\MissingServiceException("Service is not defined.");');
            }
        }
        $method->setBody('return new class ($this) ' . $class . ';');
    }
}
