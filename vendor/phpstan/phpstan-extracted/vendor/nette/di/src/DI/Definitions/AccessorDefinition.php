<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection;
/**
 * Accessor definition.
 */
final class AccessorDefinition extends \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition
{
    const METHOD_GET = 'get';
    /** @var Reference|null */
    private $reference;
    /** @return static */
    public function setImplement(string $type)
    {
        if (!\interface_exists($type)) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Service '{$this->getName()}': Interface '{$type}' not found.");
        }
        $rc = new \ReflectionClass($type);
        $method = $rc->getMethods()[0] ?? null;
        if (!$method || $method->isStatic() || $method->getName() !== self::METHOD_GET || \count($rc->getMethods()) > 1) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Service '{$this->getName()}': Interface {$type} must have just one non-static method get().");
        } elseif ($method->getNumberOfParameters()) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Service '{$this->getName()}': Method {$type}::get() must have no parameters.");
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
     * @param  string|Reference  $reference
     * @return static
     */
    public function setReference($reference)
    {
        if ($reference instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference) {
            $this->reference = $reference;
        } else {
            $this->reference = \substr($reference, 0, 1) === '@' ? new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\substr($reference, 1)) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference::fromType($reference);
        }
        return $this;
    }
    /**
     * @return \_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference|null
     */
    public function getReference()
    {
        return $this->reference;
    }
    /**
     * @return void
     */
    public function resolveType(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
    }
    /**
     * @return void
     */
    public function complete(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Resolver $resolver)
    {
        if (!$this->reference) {
            $interface = $this->getType();
            $method = new \ReflectionMethod($interface, self::METHOD_GET);
            $returnType = \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::getReturnType($method);
            if (!$returnType) {
                throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Method {$interface}::get() has not return type hint or annotation @return.");
            } elseif (!\class_exists($returnType) && !\interface_exists($returnType)) {
                throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\ServiceCreationException("Check a type hint or annotation @return of the {$interface}::get() method, class '{$returnType}' cannot be found.");
            }
            $this->setReference($returnType);
        }
        $this->reference = $resolver->normalizeReference($this->reference);
    }
    /**
     * @return void
     */
    public function generateMethod(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method $method, \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\PhpGenerator $generator)
    {
        $class = (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType())->addImplement($this->getType());
        $class->addProperty('container')->setPrivate();
        $class->addMethod('__construct')->addBody('$this->container = $container;')->addParameter('container')->setType($generator->getClassName());
        $rm = new \ReflectionMethod($this->getType(), self::METHOD_GET);
        $class->addMethod(self::METHOD_GET)->setBody('return $this->container->getService(?);', [$this->reference->getValue()])->setReturnType(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Reflection::getReturnType($rm));
        $method->setBody('return new class ($this) ' . $class . ';');
    }
}
