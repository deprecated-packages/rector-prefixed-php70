<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Instance of PHP file.
 *
 * Generates:
 * - opening tag (<?php)
 * - doc comments
 * - one or more namespaces
 */
final class PhpFile
{
    use Nette\SmartObject;
    use Traits\CommentAware;
    /** @var PhpNamespace[] */
    private $namespaces = [];
    /** @var bool */
    private $strictTypes = \false;
    public function addClass(string $name) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType
    {
        return $this->addNamespace(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractNamespace($name))->addClass(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractShortName($name));
    }
    public function addInterface(string $name) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType
    {
        return $this->addNamespace(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractNamespace($name))->addInterface(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractShortName($name));
    }
    public function addTrait(string $name) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType
    {
        return $this->addNamespace(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractNamespace($name))->addTrait(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Helpers::extractShortName($name));
    }
    /** @param  string|PhpNamespace  $namespace */
    public function addNamespace($namespace) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PhpNamespace
    {
        if ($namespace instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PhpNamespace) {
            $res = $this->namespaces[$namespace->getName()] = $namespace;
        } elseif (\is_string($namespace)) {
            $res = $this->namespaces[$namespace] = $this->namespaces[$namespace] ?? new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PhpNamespace($namespace);
        } else {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument must be string|PhpNamespace.');
        }
        foreach ($this->namespaces as $namespace) {
            $namespace->setBracketedSyntax(\count($this->namespaces) > 1 && isset($this->namespaces['']));
        }
        return $res;
    }
    /** @return PhpNamespace[] */
    public function getNamespaces() : array
    {
        return $this->namespaces;
    }
    /** @return static */
    public function addUse(string $name, string $alias = null)
    {
        $this->addNamespace('')->addUse($name, $alias);
        return $this;
    }
    /**
     * Adds declare(strict_types=1) to output.
     * @return static
     */
    public function setStrictTypes(bool $on = \true)
    {
        $this->strictTypes = $on;
        return $this;
    }
    public function hasStrictTypes() : bool
    {
        return $this->strictTypes;
    }
    /** @deprecated  use hasStrictTypes() */
    public function getStrictTypes() : bool
    {
        return $this->strictTypes;
    }
    public function __toString() : string
    {
        try {
            return (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Printer())->printFile($this);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", \E_USER_ERROR);
            return '';
        }
    }
}
