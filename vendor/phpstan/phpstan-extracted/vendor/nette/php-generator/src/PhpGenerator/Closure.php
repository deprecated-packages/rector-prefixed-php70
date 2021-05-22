<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Closure.
 *
 * @property string $body
 */
final class Closure
{
    use Nette\SmartObject;
    use Traits\FunctionLike;
    use Traits\AttributeAware;
    /** @var Parameter[] */
    private $uses = [];
    /**
     * @return $this
     */
    public static function from(\Closure $closure)
    {
        return (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromFunctionReflection(new \ReflectionFunction($closure));
    }
    public function __toString() : string
    {
        try {
            return (new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Printer())->printClosure($this);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", \E_USER_ERROR);
            return '';
        }
    }
    /**
     * @param  Parameter[]  $uses
     * @return static
     */
    public function setUses(array $uses)
    {
        (function (\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Parameter ...$uses) {
        })(...$uses);
        $this->uses = $uses;
        return $this;
    }
    public function getUses() : array
    {
        return $this->uses;
    }
    public function addUse(string $name) : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Parameter
    {
        return $this->uses[] = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Parameter($name);
    }
}
