<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\NodeCompiler;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\Reflector;
use RuntimeException;
class CompilerContext
{
    /** @var Reflector */
    private $reflector;
    /** @var string|null */
    private $fileName;
    /** @var ReflectionClass|null */
    private $self;
    /** @var string|null */
    private $namespace;
    /** @var string|null */
    private $functionName;
    /**
     * @param string|null $fileName
     * @param \PHPStan\BetterReflection\Reflection\ReflectionClass|null $self
     * @param string|null $namespace
     * @param string|null $functionName
     */
    public function __construct(\PHPStan\BetterReflection\Reflector\Reflector $reflector, $fileName, $self, $namespace, $functionName)
    {
        $this->reflector = $reflector;
        $this->fileName = $fileName;
        $this->self = $self;
        $this->namespace = $namespace;
        $this->functionName = $functionName;
    }
    /**
     * Does the current context have a "self" or "this"
     *
     * (e.g. if the context is a function, then no, there will be no self)
     */
    public function hasSelf() : bool
    {
        return $this->self !== null;
    }
    public function getSelf() : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        if (!$this->hasSelf()) {
            throw new \RuntimeException('The current context does not have a class for self');
        }
        return $this->self;
    }
    public function getReflector() : \PHPStan\BetterReflection\Reflector\Reflector
    {
        return $this->reflector;
    }
    public function hasFileName() : bool
    {
        return $this->fileName !== null;
    }
    public function getFileName() : string
    {
        if (!$this->hasFileName()) {
            throw new \RuntimeException('The current context does not have a filename');
        }
        return $this->fileName;
    }
    /**
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    /**
     * @return string|null
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }
}
