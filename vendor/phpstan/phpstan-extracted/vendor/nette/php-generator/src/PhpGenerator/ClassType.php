<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Class/Interface/Trait description.
 *
 * @property Method[] $methods
 * @property Property[] $properties
 */
final class ClassType
{
    use Nette\SmartObject;
    use Traits\CommentAware;
    use Traits\AttributeAware;
    const TYPE_CLASS = 'class', TYPE_INTERFACE = 'interface', TYPE_TRAIT = 'trait';
    const VISIBILITY_PUBLIC = 'public', VISIBILITY_PROTECTED = 'protected', VISIBILITY_PRIVATE = 'private';
    /** @var PhpNamespace|null */
    private $namespace;
    /** @var string|null */
    private $name;
    /** @var string  class|interface|trait */
    private $type = self::TYPE_CLASS;
    /** @var bool */
    private $final = \false;
    /** @var bool */
    private $abstract = \false;
    /** @var string|string[] */
    private $extends = [];
    /** @var string[] */
    private $implements = [];
    /** @var array[] */
    private $traits = [];
    /** @var Constant[] name => Constant */
    private $consts = [];
    /** @var Property[] name => Property */
    private $properties = [];
    /** @var Method[] name => Method */
    private $methods = [];
    /**
     * @param  string|object  $class
     * @return $this
     */
    public static function from($class)
    {
        return (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromClassReflection(new \ReflectionClass($class));
    }
    /**
     * @param  string|object  $class
     * @return $this
     */
    public static function withBodiesFrom($class)
    {
        return (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromClassReflection(new \ReflectionClass($class), \true);
    }
    public function __construct(string $name = null, \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PhpNamespace $namespace = null)
    {
        $this->setName($name);
        $this->namespace = $namespace;
    }
    public function __toString() : string
    {
        try {
            return (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Printer())->printClass($this, $this->namespace);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", \E_USER_ERROR);
            return '';
        }
    }
    /** @deprecated  an object can be in multiple namespaces
     * @return \_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PhpNamespace|null */
    public function getNamespace()
    {
        return $this->namespace;
    }
    /** @return static
     * @param string|null $name */
    public function setName($name)
    {
        if ($name !== null && !\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::isIdentifier($name)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Value '{$name}' is not valid class name.");
        }
        $this->name = $name;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
    /** @return static */
    public function setClass()
    {
        $this->type = self::TYPE_CLASS;
        return $this;
    }
    public function isClass() : bool
    {
        return $this->type === self::TYPE_CLASS;
    }
    /** @return static */
    public function setInterface()
    {
        $this->type = self::TYPE_INTERFACE;
        return $this;
    }
    public function isInterface() : bool
    {
        return $this->type === self::TYPE_INTERFACE;
    }
    /** @return static */
    public function setTrait()
    {
        $this->type = self::TYPE_TRAIT;
        return $this;
    }
    public function isTrait() : bool
    {
        return $this->type === self::TYPE_TRAIT;
    }
    /** @return static */
    public function setType(string $type)
    {
        if (!\in_array($type, [self::TYPE_CLASS, self::TYPE_INTERFACE, self::TYPE_TRAIT], \true)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be class|interface|trait.');
        }
        $this->type = $type;
        return $this;
    }
    public function getType() : string
    {
        return $this->type;
    }
    /** @return static */
    public function setFinal(bool $state = \true)
    {
        $this->final = $state;
        return $this;
    }
    public function isFinal() : bool
    {
        return $this->final;
    }
    /** @return static */
    public function setAbstract(bool $state = \true)
    {
        $this->abstract = $state;
        return $this;
    }
    public function isAbstract() : bool
    {
        return $this->abstract;
    }
    /**
     * @param  string|string[]  $names
     * @return static
     */
    public function setExtends($names)
    {
        if (!\is_string($names) && !\is_array($names)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be string or string[].');
        }
        $this->validateNames((array) $names);
        $this->extends = $names;
        return $this;
    }
    /** @return string|string[] */
    public function getExtends()
    {
        return $this->extends;
    }
    /** @return static */
    public function addExtend(string $name)
    {
        $this->validateNames([$name]);
        $this->extends = (array) $this->extends;
        $this->extends[] = $name;
        return $this;
    }
    /**
     * @param  string[]  $names
     * @return static
     */
    public function setImplements(array $names)
    {
        $this->validateNames($names);
        $this->implements = $names;
        return $this;
    }
    /** @return string[] */
    public function getImplements() : array
    {
        return $this->implements;
    }
    /** @return static */
    public function addImplement(string $name)
    {
        $this->validateNames([$name]);
        $this->implements[] = $name;
        return $this;
    }
    /** @return static */
    public function removeImplement(string $name)
    {
        $key = \array_search($name, $this->implements, \true);
        if ($key !== \false) {
            unset($this->implements[$key]);
        }
        return $this;
    }
    /**
     * @param  string[]  $names
     * @return static
     */
    public function setTraits(array $names)
    {
        $this->validateNames($names);
        $this->traits = \array_fill_keys($names, []);
        return $this;
    }
    /** @return string[] */
    public function getTraits() : array
    {
        return \array_keys($this->traits);
    }
    /** @internal */
    public function getTraitResolutions() : array
    {
        return $this->traits;
    }
    /** @return static */
    public function addTrait(string $name, array $resolutions = [])
    {
        $this->validateNames([$name]);
        $this->traits[$name] = $resolutions;
        return $this;
    }
    /** @return static */
    public function removeTrait(string $name)
    {
        unset($this->traits[$name]);
        return $this;
    }
    /**
     * @param  Method|Property|Constant  $member
     * @return static
     */
    public function addMember($member)
    {
        if ($member instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method) {
            if ($this->isInterface()) {
                $member->setBody(null);
            }
            $this->methods[$member->getName()] = $member;
        } elseif ($member instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Property) {
            $this->properties[$member->getName()] = $member;
        } elseif ($member instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Constant) {
            $this->consts[$member->getName()] = $member;
        } else {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be Method|Property|Constant.');
        }
        return $this;
    }
    /**
     * @param  Constant[]|mixed[]  $consts
     * @return static
     */
    public function setConstants(array $consts)
    {
        $this->consts = [];
        foreach ($consts as $k => $v) {
            $const = $v instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Constant ? $v : (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Constant($k))->setValue($v);
            $this->consts[$const->getName()] = $const;
        }
        return $this;
    }
    /** @return Constant[] */
    public function getConstants() : array
    {
        return $this->consts;
    }
    public function addConstant(string $name, $value) : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Constant
    {
        return $this->consts[$name] = (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Constant($name))->setValue($value);
    }
    /** @return static */
    public function removeConstant(string $name)
    {
        unset($this->consts[$name]);
        return $this;
    }
    /**
     * @param  Property[]  $props
     * @return static
     */
    public function setProperties(array $props)
    {
        $this->properties = [];
        foreach ($props as $v) {
            if (!$v instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Property) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be Nette\\PhpGenerator\\Property[].');
            }
            $this->properties[$v->getName()] = $v;
        }
        return $this;
    }
    /** @return Property[] */
    public function getProperties() : array
    {
        return $this->properties;
    }
    public function getProperty(string $name) : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Property
    {
        if (!isset($this->properties[$name])) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Property '{$name}' not found.");
        }
        return $this->properties[$name];
    }
    /**
     * @param  string  $name  without $
     */
    public function addProperty(string $name, $value = null) : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Property
    {
        return $this->properties[$name] = (new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Property($name))->setValue($value);
    }
    /**
     * @param  string  $name without $
     * @return static
     */
    public function removeProperty(string $name)
    {
        unset($this->properties[$name]);
        return $this;
    }
    public function hasProperty(string $name) : bool
    {
        return isset($this->properties[$name]);
    }
    /**
     * @param  Method[]  $methods
     * @return static
     */
    public function setMethods(array $methods)
    {
        $this->methods = [];
        foreach ($methods as $v) {
            if (!$v instanceof \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be Nette\\PhpGenerator\\Method[].');
            }
            $this->methods[$v->getName()] = $v;
        }
        return $this;
    }
    /** @return Method[] */
    public function getMethods() : array
    {
        return $this->methods;
    }
    public function getMethod(string $name) : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method
    {
        if (!isset($this->methods[$name])) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Method '{$name}' not found.");
        }
        return $this->methods[$name];
    }
    public function addMethod(string $name) : \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method
    {
        $method = new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Method($name);
        if ($this->isInterface()) {
            $method->setBody(null);
        } else {
            $method->setPublic();
        }
        return $this->methods[$name] = $method;
    }
    /** @return static */
    public function removeMethod(string $name)
    {
        unset($this->methods[$name]);
        return $this;
    }
    public function hasMethod(string $name) : bool
    {
        return isset($this->methods[$name]);
    }
    /** @throws Nette\InvalidStateException
     * @return void */
    public function validate()
    {
        if ($this->abstract && $this->final) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException('Class cannot be abstract and final.');
        } elseif (!$this->name && ($this->abstract || $this->final)) {
            throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException('Anonymous class cannot be abstract or final.');
        }
    }
    /**
     * @return void
     */
    private function validateNames(array $names)
    {
        foreach ($names as $name) {
            if (!\RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::isNamespaceIdentifier($name, \true)) {
                throw new \RectorPrefix20210523\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException("Value '{$name}' is not valid class name.");
            }
        }
    }
    public function __clone()
    {
        $clone = function ($item) {
            return clone $item;
        };
        $this->consts = \array_map($clone, $this->consts);
        $this->properties = \array_map($clone, $this->properties);
        $this->methods = \array_map($clone, $this->methods);
    }
}
