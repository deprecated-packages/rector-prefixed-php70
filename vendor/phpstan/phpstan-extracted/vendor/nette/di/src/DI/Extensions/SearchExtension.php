<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Extensions;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Loaders\RobotLoader;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect;
use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Arrays;
/**
 * Services auto-discovery.
 */
final class SearchExtension extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\CompilerExtension
{
    /** @var array */
    private $classes = [];
    /** @var string */
    private $tempDir;
    public function __construct(string $tempDir)
    {
        $this->tempDir = $tempDir;
    }
    public function getConfigSchema() : \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Schema
    {
        return \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::arrayOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::structure(['in' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->required(), 'files' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'classes' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'extends' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'implements' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'exclude' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::structure(['classes' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'extends' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([]), 'implements' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::anyOf(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::listOf('string'), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::string()->castTo('array'))->default([])]), 'tags' => \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Schema\Expect::array()]))->before(function ($val) {
            return \is_string($val['in'] ?? null) ? ['default' => $val] : $val;
        });
    }
    public function loadConfiguration()
    {
        foreach (\array_filter($this->config) as $name => $batch) {
            if (!\is_dir($batch->in)) {
                throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\InvalidConfigurationException("Option '{$this->name} › {$name} › in' must be valid directory name, '{$batch->in}' given.");
            }
            foreach ($this->findClasses($batch) as $class) {
                $this->classes[$class] = \array_merge($this->classes[$class] ?? [], $batch->tags);
            }
        }
    }
    public function findClasses(\stdClass $config) : array
    {
        $robot = new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Loaders\RobotLoader();
        $robot->setTempDirectory($this->tempDir);
        $robot->addDirectory($config->in);
        $robot->acceptFiles = $config->files ?: ['*.php'];
        $robot->reportParseErrors(\false);
        $robot->refresh();
        $classes = \array_unique(\array_keys($robot->getIndexedClasses()));
        $exclude = $config->exclude;
        $acceptRE = self::buildNameRegexp($config->classes);
        $rejectRE = self::buildNameRegexp($exclude->classes);
        $acceptParent = \array_merge($config->extends, $config->implements);
        $rejectParent = \array_merge($exclude->extends, $exclude->implements);
        $found = [];
        foreach ($classes as $class) {
            if (!\class_exists($class) && !\interface_exists($class) && !\trait_exists($class)) {
                throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\InvalidStateException("Class {$class} was found, but it cannot be loaded by autoloading.");
            }
            $rc = new \ReflectionClass($class);
            if (($rc->isInstantiable() || $rc->isInterface() && \count($methods = $rc->getMethods()) === 1 && $methods[0]->name === 'create') && (!$acceptRE || \preg_match($acceptRE, $rc->name)) && (!$rejectRE || !\preg_match($rejectRE, $rc->name)) && (!$acceptParent || \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Arrays::some($acceptParent, function ($nm) use($rc) {
                return $rc->isSubclassOf($nm);
            })) && (!$rejectParent || \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Arrays::every($rejectParent, function ($nm) use($rc) {
                return !$rc->isSubclassOf($nm);
            }))) {
                $found[] = $rc->name;
            }
        }
        return $found;
    }
    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->classes as $class => $foo) {
            if ($builder->findByType($class)) {
                unset($this->classes[$class]);
            }
        }
        foreach ($this->classes as $class => $tags) {
            if (\class_exists($class)) {
                $def = $builder->addDefinition(null)->setType($class);
            } else {
                $def = $builder->addFactoryDefinition(null)->setImplement($class);
            }
            $def->setTags(\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Arrays::normalize($tags, \true));
        }
    }
    /**
     * @return string|null
     */
    private static function buildNameRegexp(array $masks)
    {
        $res = [];
        foreach ((array) $masks as $mask) {
            $mask = (\strpos($mask, '\\') === \false ? '**\\' : '') . $mask;
            $mask = \preg_quote($mask, '#');
            $mask = \str_replace('\\*\\*\\\\', '(.*\\\\)?', $mask);
            $mask = \str_replace('\\\\\\*\\*', '(\\\\.*)?', $mask);
            $mask = \str_replace('\\*', '\\w*', $mask);
            $res[] = $mask;
        }
        return $res ? '#^(' . \implode('|', $res) . ')$#i' : null;
    }
}
