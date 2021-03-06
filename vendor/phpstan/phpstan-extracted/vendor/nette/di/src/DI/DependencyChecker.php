<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection;
use ReflectionClass;
use ReflectionMethod;
/**
 * Cache dependencies checker.
 */
class DependencyChecker
{
    use Nette\SmartObject;
    const VERSION = 1;
    /** @var array of ReflectionClass|\ReflectionFunctionAbstract|string */
    private $dependencies = [];
    /**
     * Adds dependencies to the list.
     * @return static
     */
    public function add(array $deps)
    {
        $this->dependencies = \array_merge($this->dependencies, $deps);
        return $this;
    }
    /**
     * Exports dependencies.
     */
    public function export() : array
    {
        $files = $phpFiles = $classes = $functions = [];
        foreach ($this->dependencies as $dep) {
            if (\is_string($dep)) {
                $files[] = $dep;
            } elseif ($dep instanceof \ReflectionClass) {
                if (empty($classes[$name = $dep->name])) {
                    $all = [$name] + \class_parents($name) + \class_implements($name);
                    foreach ($all as &$item) {
                        $all += \class_uses($item);
                        $phpFiles[] = (new \ReflectionClass($item))->getFileName();
                        $classes[$item] = \true;
                    }
                }
            } elseif ($dep instanceof \ReflectionFunctionAbstract) {
                $phpFiles[] = $dep->getFileName();
                $functions[] = \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::toString($dep);
            } else {
                throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\InvalidStateException('Unexpected dependency ' . \gettype($dep));
            }
        }
        $classes = \array_keys($classes);
        $functions = \array_unique($functions, \SORT_REGULAR);
        $hash = self::calculateHash($classes, $functions);
        $files = @\array_map('filemtime', \array_combine($files, $files));
        // @ - file may not exist
        $phpFiles = @\array_map('filemtime', \array_combine($phpFiles, $phpFiles));
        // @ - file may not exist
        return [self::VERSION, $files, $phpFiles, $classes, $functions, $hash];
    }
    /**
     * Are dependencies expired?
     */
    public static function isExpired(int $version, array $files, array &$phpFiles, array $classes, array $functions, string $hash) : bool
    {
        try {
            $currentFiles = @\array_map('filemtime', \array_combine($tmp = \array_keys($files), $tmp));
            // @ - files may not exist
            $origPhpFiles = $phpFiles;
            $phpFiles = @\array_map('filemtime', \array_combine($tmp = \array_keys($phpFiles), $tmp));
            // @ - files may not exist
            return $version !== self::VERSION || $files !== $currentFiles || $phpFiles !== $origPhpFiles && $hash !== self::calculateHash($classes, $functions);
        } catch (\ReflectionException $e) {
            return \true;
        }
    }
    private static function calculateHash(array $classes, array $functions) : string
    {
        $hash = [];
        foreach ($classes as $name) {
            $class = new \ReflectionClass($name);
            $hash[] = [$name, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getUseStatements($class), $class->isAbstract(), \get_parent_class($name), \class_implements($name), \class_uses($name)];
            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
                if ($prop->getDeclaringClass() == $class) {
                    // intentionally ==
                    $hash[] = [$name, $prop->name, $prop->getDocComment()];
                }
            }
            foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass() == $class) {
                    // intentionally ==
                    $hash[] = [$name, $method->name, $method->getDocComment(), self::hashParameters($method), $method->hasReturnType() ? [$method->getReturnType()->getName(), $method->getReturnType()->allowsNull()] : null];
                }
            }
        }
        $flip = \array_flip($classes);
        foreach ($functions as $name) {
            if (\strpos($name, '::')) {
                $method = new \ReflectionMethod($name);
                $class = $method->getDeclaringClass();
                if (isset($flip[$class->name])) {
                    continue;
                }
                $uses = \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getUseStatements($class);
            } else {
                $method = new \ReflectionFunction($name);
                $uses = null;
            }
            $hash[] = [$name, $uses, $method->getDocComment(), self::hashParameters($method), $method->hasReturnType() ? [$method->getReturnType()->getName(), $method->getReturnType()->allowsNull()] : null];
        }
        return \md5(\serialize($hash));
    }
    private static function hashParameters(\ReflectionFunctionAbstract $method) : array
    {
        $res = [];
        foreach ($method->getParameters() as $param) {
            $res[] = [$param->name, \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getParameterType($param), $param->allowsNull(), $param->isVariadic(), $param->isDefaultValueAvailable() ? [\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Reflection::getParameterDefaultValue($param)] : null];
        }
        return $res;
    }
}
