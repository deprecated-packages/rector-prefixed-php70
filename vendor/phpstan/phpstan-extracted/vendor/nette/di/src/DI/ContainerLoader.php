<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette;
/**
 * DI container loader.
 */
class ContainerLoader
{
    use Nette\SmartObject;
    /** @var bool */
    private $autoRebuild = \false;
    /** @var string */
    private $tempDirectory;
    public function __construct(string $tempDirectory, bool $autoRebuild = \false)
    {
        $this->tempDirectory = $tempDirectory;
        $this->autoRebuild = $autoRebuild;
    }
    /**
     * @param  callable  $generator  function (Nette\DI\Compiler $compiler): string|null
     * @param  mixed  $key
     */
    public function load(callable $generator, $key = null) : string
    {
        $class = $this->getClassName($key);
        if (!\class_exists($class, \false)) {
            $this->loadFile($class, $generator);
        }
        return $class;
    }
    /**
     * @param  mixed  $key
     */
    public function getClassName($key) : string
    {
        return 'Container_' . \substr(\md5(\serialize($key)), 0, 10);
    }
    /**
     * @return void
     */
    private function loadFile(string $class, callable $generator)
    {
        $file = "{$this->tempDirectory}/{$class}.php";
        if (!$this->isExpired($file) && @(include $file) !== \false) {
            // @ file may not exist
            return;
        }
        \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\FileSystem::createDir($this->tempDirectory);
        $handle = @\fopen("{$file}.lock", 'c+');
        // @ is escalated to exception
        if (!$handle) {
            throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\IOException("Unable to create file '{$file}.lock'. " . \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Helpers::getLastError());
        } elseif (!@\flock($handle, \LOCK_EX)) {
            // @ is escalated to exception
            throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\IOException("Unable to acquire exclusive lock on '{$file}.lock'. " . \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Helpers::getLastError());
        }
        if (!\is_file($file) || $this->isExpired($file, $updatedMeta)) {
            if (isset($updatedMeta)) {
                $toWrite["{$file}.meta"] = $updatedMeta;
            } else {
                list($toWrite[$file], $toWrite["{$file}.meta"]) = $this->generate($class, $generator);
            }
            foreach ($toWrite as $name => $content) {
                if (\file_put_contents("{$name}.tmp", $content) !== \strlen($content) || !\rename("{$name}.tmp", $name)) {
                    @\unlink("{$name}.tmp");
                    // @ - file may not exist
                    throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\IOException("Unable to create file '{$name}'.");
                } elseif (\function_exists('opcache_invalidate')) {
                    @\opcache_invalidate($name, \true);
                    // @ can be restricted
                }
            }
        }
        if (@(include $file) === \false) {
            // @ - error escalated to exception
            throw new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\IOException("Unable to include '{$file}'.");
        }
        \flock($handle, \LOCK_UN);
    }
    private function isExpired(string $file, string &$updatedMeta = null) : bool
    {
        if ($this->autoRebuild) {
            $meta = @\unserialize((string) \file_get_contents("{$file}.meta"));
            // @ - file may not exist
            $orig = $meta[2] ?? null;
            return empty($meta[0]) || \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\DependencyChecker::isExpired(...$meta) || $orig !== $meta[2] && ($updatedMeta = \serialize($meta));
        }
        return \false;
    }
    /** @return array of (code, file[]) */
    protected function generate(string $class, callable $generator) : array
    {
        $compiler = new \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\DI\Compiler();
        $compiler->setClassName($class);
        $code = $generator(...[&$compiler]) ?: $compiler->compile();
        return ["<?php\n{$code}", \serialize($compiler->exportDependencies())];
    }
}
