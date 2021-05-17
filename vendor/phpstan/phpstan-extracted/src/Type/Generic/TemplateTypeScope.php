<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

class TemplateTypeScope
{
    /** @var string|null */
    private $className;
    /** @var string|null */
    private $functionName;
    /**
     * @return $this
     */
    public static function createWithFunction(string $functionName)
    {
        return new self(null, $functionName);
    }
    /**
     * @return $this
     */
    public static function createWithMethod(string $className, string $functionName)
    {
        return new self($className, $functionName);
    }
    /**
     * @return $this
     */
    public static function createWithClass(string $className)
    {
        return new self($className, null);
    }
    /**
     * @param string|null $className
     * @param string|null $functionName
     */
    private function __construct($className, $functionName)
    {
        $this->className = $className;
        $this->functionName = $functionName;
    }
    /**
     * @return string|null
     */
    public function getClassName()
    {
        return $this->className;
    }
    /**
     * @return string|null
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }
    /**
     * @param $this $other
     */
    public function equals($other) : bool
    {
        return $this->className === $other->className && $this->functionName === $other->functionName;
    }
    public function describe() : string
    {
        if ($this->className === null) {
            return \sprintf('function %s()', $this->functionName);
        }
        if ($this->functionName === null) {
            return \sprintf('class %s', $this->className);
        }
        return \sprintf('method %s::%s()', $this->className, $this->functionName);
    }
    /**
     * @param mixed[] $properties
     * @return $this
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['className'], $properties['functionName']);
    }
}
