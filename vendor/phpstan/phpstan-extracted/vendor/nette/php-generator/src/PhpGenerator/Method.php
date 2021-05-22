<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Class method.
 *
 * @property string|null $body
 */
final class Method
{
    use Nette\SmartObject;
    use Traits\FunctionLike;
    use Traits\NameAware;
    use Traits\VisibilityAware;
    use Traits\CommentAware;
    use Traits\AttributeAware;
    /** @var string|null */
    private $body = '';
    /** @var bool */
    private $static = \false;
    /** @var bool */
    private $final = \false;
    /** @var bool */
    private $abstract = \false;
    /**
     * @param  string|array  $method
     * @return $this
     */
    public static function from($method)
    {
        return (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromMethodReflection(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Callback::toReflection($method));
    }
    public function __toString() : string
    {
        try {
            return (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Printer())->printMethod($this);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", \E_USER_ERROR);
            return '';
        }
    }
    /** @return static
     * @param string|null $code
     * @param mixed[]|null $args */
    public function setBody($code, $args = null)
    {
        $this->body = $args === null || $code === null ? $code : (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Dumper())->format($code, ...$args);
        return $this;
    }
    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }
    /** @return static */
    public function setStatic(bool $state = \true)
    {
        $this->static = $state;
        return $this;
    }
    public function isStatic() : bool
    {
        return $this->static;
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
     * @param  string  $name without $
     */
    public function addPromotedParameter(string $name, $defaultValue = null) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PromotedParameter
    {
        $param = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\PromotedParameter($name);
        if (\func_num_args() > 1) {
            $param->setDefaultValue($defaultValue);
        }
        return $this->parameters[$name] = $param;
    }
    /** @throws Nette\InvalidStateException
     * @return void */
    public function validate()
    {
        if ($this->abstract && ($this->final || $this->visibility === \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\ClassType::VISIBILITY_PRIVATE)) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidStateException('Method cannot be abstract and final or private.');
        }
    }
}
