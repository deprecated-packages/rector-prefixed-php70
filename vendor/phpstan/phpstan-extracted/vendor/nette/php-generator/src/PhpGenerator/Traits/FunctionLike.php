<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Traits;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Dumper;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Parameter;
/**
 * @internal
 */
trait FunctionLike
{
    /** @var string */
    private $body = '';
    /** @var Parameter[] */
    private $parameters = [];
    /** @var bool */
    private $variadic = \false;
    /** @var string|null */
    private $returnType;
    /** @var bool */
    private $returnReference = \false;
    /** @var bool */
    private $returnNullable = \false;
    /** @return static */
    public function setBody(string $code, array $args = null)
    {
        $this->body = $args === null ? $code : (new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Dumper())->format($code, ...$args);
        return $this;
    }
    public function getBody() : string
    {
        return $this->body;
    }
    /** @return static */
    public function addBody(string $code, array $args = null)
    {
        $this->body .= ($args === null ? $code : (new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Dumper())->format($code, ...$args)) . "\n";
        return $this;
    }
    /**
     * @param  Parameter[]  $val
     * @return static
     */
    public function setParameters(array $val)
    {
        $this->parameters = [];
        foreach ($val as $v) {
            if (!$v instanceof \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Parameter) {
                throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\InvalidArgumentException('Argument must be Nette\\PhpGenerator\\Parameter[].');
            }
            $this->parameters[$v->getName()] = $v;
        }
        return $this;
    }
    /** @return Parameter[] */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    /**
     * @param  string  $name without $
     */
    public function addParameter(string $name, $defaultValue = null) : \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Parameter
    {
        $param = new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\Parameter($name);
        if (\func_num_args() > 1) {
            $param->setDefaultValue($defaultValue);
        }
        return $this->parameters[$name] = $param;
    }
    /**
     * @param  string  $name without $
     * @return static
     */
    public function removeParameter(string $name)
    {
        unset($this->parameters[$name]);
        return $this;
    }
    /** @return static */
    public function setVariadic(bool $state = \true)
    {
        $this->variadic = $state;
        return $this;
    }
    public function isVariadic() : bool
    {
        return $this->variadic;
    }
    /** @return static
     * @param string|null $val */
    public function setReturnType($val)
    {
        $this->returnType = $val;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
    /** @return static */
    public function setReturnReference(bool $state = \true)
    {
        $this->returnReference = $state;
        return $this;
    }
    public function getReturnReference() : bool
    {
        return $this->returnReference;
    }
    /** @return static */
    public function setReturnNullable(bool $state = \true)
    {
        $this->returnNullable = $state;
        return $this;
    }
    public function isReturnNullable() : bool
    {
        return $this->returnNullable;
    }
    /** @deprecated  use isReturnNullable() */
    public function getReturnNullable() : bool
    {
        return $this->returnNullable;
    }
    /** @deprecated
     * @return $this */
    public function setNamespace(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\PhpGenerator\PhpNamespace $val = null)
    {
        \trigger_error(__METHOD__ . '() is deprecated', \E_USER_DEPRECATED);
        return $this;
    }
}
