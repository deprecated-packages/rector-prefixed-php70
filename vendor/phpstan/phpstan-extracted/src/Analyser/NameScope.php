<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;
class NameScope
{
    /** @var string|null */
    private $namespace;
    /** @var array<string, string> alias(string) => fullName(string) */
    private $uses;
    /** @var string|null */
    private $className;
    /** @var string|null */
    private $functionName;
    /** @var TemplateTypeMap */
    private $templateTypeMap;
    /** @var array<string, true> */
    private $typeAliasesMap;
    /** @var bool */
    private $bypassTypeAliases;
    /**
     * @param string|null $namespace
     * @param array<string, string> $uses alias(string) => fullName(string)
     * @param string|null $className
     * @param array<string, true> $typeAliasesMap
     * @param string|null $functionName
     * @param \PHPStan\Type\Generic\TemplateTypeMap|null $templateTypeMap
     */
    public function __construct($namespace, array $uses, $className = null, $functionName = null, $templateTypeMap = null, array $typeAliasesMap = [], bool $bypassTypeAliases = \false)
    {
        $this->namespace = $namespace;
        $this->uses = $uses;
        $this->className = $className;
        $this->functionName = $functionName;
        $this->templateTypeMap = $templateTypeMap ?? \PHPStan\Type\Generic\TemplateTypeMap::createEmpty();
        $this->typeAliasesMap = $typeAliasesMap;
        $this->bypassTypeAliases = $bypassTypeAliases;
    }
    /**
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    /**
     * @return array<string, string>
     */
    public function getUses() : array
    {
        return $this->uses;
    }
    public function hasUseAlias(string $name) : bool
    {
        return isset($this->uses[\strtolower($name)]);
    }
    /**
     * @return string|null
     */
    public function getClassName()
    {
        return $this->className;
    }
    public function resolveStringName(string $name) : string
    {
        if (\strpos($name, '\\') === 0) {
            return \ltrim($name, '\\');
        }
        $nameParts = \explode('\\', $name);
        $firstNamePart = \strtolower($nameParts[0]);
        if (isset($this->uses[$firstNamePart])) {
            if (\count($nameParts) === 1) {
                return $this->uses[$firstNamePart];
            }
            \array_shift($nameParts);
            return \sprintf('%s\\%s', $this->uses[$firstNamePart], \implode('\\', $nameParts));
        }
        if ($this->namespace !== null) {
            return \sprintf('%s\\%s', $this->namespace, $name);
        }
        return $name;
    }
    /**
     * @return \PHPStan\Type\Generic\TemplateTypeScope|null
     */
    public function getTemplateTypeScope()
    {
        if ($this->className !== null) {
            if ($this->functionName !== null) {
                return \PHPStan\Type\Generic\TemplateTypeScope::createWithMethod($this->className, $this->functionName);
            }
            return \PHPStan\Type\Generic\TemplateTypeScope::createWithClass($this->className);
        }
        if ($this->functionName !== null) {
            return \PHPStan\Type\Generic\TemplateTypeScope::createWithFunction($this->functionName);
        }
        return null;
    }
    public function getTemplateTypeMap() : \PHPStan\Type\Generic\TemplateTypeMap
    {
        return $this->templateTypeMap;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolveTemplateTypeName(string $name)
    {
        return $this->templateTypeMap->getType($name);
    }
    /**
     * @return $this
     */
    public function withTemplateTypeMap(\PHPStan\Type\Generic\TemplateTypeMap $map)
    {
        if ($map->isEmpty()) {
            return $this;
        }
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, new \PHPStan\Type\Generic\TemplateTypeMap(\array_merge($this->templateTypeMap->getTypes(), $map->getTypes())), $this->typeAliasesMap);
    }
    /**
     * @return $this
     */
    public function unsetTemplateType(string $name)
    {
        $map = $this->templateTypeMap;
        if (!$map->hasType($name)) {
            return $this;
        }
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, $this->templateTypeMap->unsetType($name), $this->typeAliasesMap);
    }
    /**
     * @return $this
     */
    public function bypassTypeAliases()
    {
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, $this->templateTypeMap, $this->typeAliasesMap, \true);
    }
    public function shouldBypassTypeAliases() : bool
    {
        return $this->bypassTypeAliases;
    }
    public function hasTypeAlias(string $alias) : bool
    {
        return \array_key_exists($alias, $this->typeAliasesMap);
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['namespace'], $properties['uses'], $properties['className'], $properties['functionName'], $properties['templateTypeMap'], $properties['typeAliasesMap']);
    }
}
