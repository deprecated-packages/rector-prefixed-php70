<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Adapter;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Helpers;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon;
use PHPStan\File\FileHelper;
use PHPStan\File\FileReader;
class NeonAdapter implements \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Adapter
{
    const CACHE_KEY = 'v11-excludePaths';
    const PREVENT_MERGING_SUFFIX = '!';
    /** @var FileHelper[] */
    private $fileHelpers = [];
    /**
     * @param string $file
     * @return mixed[]
     */
    public function load(string $file) : array
    {
        $contents = \PHPStan\File\FileReader::read($file);
        try {
            return $this->process((array) \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::decode($contents), '', $file);
        } catch (\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Exception $e) {
            throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Exception(\sprintf('Error while loading %s: %s', $file, $e->getMessage()));
        }
    }
    /**
     * @param mixed[] $arr
     * @return mixed[]
     */
    public function process(array $arr, string $fileKey, string $file) : array
    {
        $res = [];
        foreach ($arr as $key => $val) {
            if (\is_string($key) && \substr($key, -1) === self::PREVENT_MERGING_SUFFIX) {
                if (!\is_array($val) && $val !== null) {
                    throw new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\InvalidConfigurationException(\sprintf('Replacing operator is available only for arrays, item \'%s\' is not array.', $key));
                }
                $key = \substr($key, 0, -1);
                $val[\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Config\Helpers::PREVENT_MERGING] = \true;
            }
            if (\is_array($val)) {
                if (!\is_int($key)) {
                    $fileKeyToPass = $fileKey . '[' . $key . ']';
                } else {
                    $fileKeyToPass = $fileKey . '[]';
                }
                $val = $this->process($val, $fileKeyToPass, $file);
            } elseif ($val instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity) {
                if (!\is_int($key)) {
                    $fileKeyToPass = $fileKey . '(' . $key . ')';
                } else {
                    $fileKeyToPass = $fileKey . '()';
                }
                if ($val->value === \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::CHAIN) {
                    $tmp = null;
                    foreach ($this->process($val->attributes, $fileKeyToPass, $file) as $st) {
                        $tmp = new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement($tmp === null ? $st->getEntity() : [$tmp, \ltrim(\implode('::', (array) $st->getEntity()), ':')], $st->arguments);
                    }
                    $val = $tmp;
                } else {
                    $tmp = $this->process([$val->value], $fileKeyToPass, $file);
                    $val = new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement($tmp[0], $this->process($val->attributes, $fileKeyToPass, $file));
                }
            }
            $keyToResolve = $fileKey;
            if (\is_int($key)) {
                $keyToResolve .= '[]';
            } else {
                $keyToResolve .= '[' . $key . ']';
            }
            if (\in_array($keyToResolve, ['[parameters][autoload_files][]', '[parameters][autoload_directories][]', '[parameters][paths][]', '[parameters][excludes_analyse][]', '[parameters][excludePaths][]', '[parameters][excludePaths][analyse][]', '[parameters][excludePaths][analyseAndScan][]', '[parameters][ignoreErrors][][paths][]', '[parameters][ignoreErrors][][path]', '[parameters][bootstrap]', '[parameters][bootstrapFiles][]', '[parameters][scanFiles][]', '[parameters][scanDirectories][]', '[parameters][tmpDir]', '[parameters][memoryLimitFile]', '[parameters][benchmarkFile]', '[parameters][stubFiles][]', '[parameters][symfony][console_application_loader]', '[parameters][symfony][container_xml_path]', '[parameters][doctrine][objectManagerLoader]'], \true) && \is_string($val) && \strpos($val, '%') === \false && \strpos($val, '*') !== 0) {
                $fileHelper = $this->createFileHelperByFile($file);
                $val = $fileHelper->normalizePath($fileHelper->absolutizePath($val));
            }
            $res[$key] = $val;
        }
        return $res;
    }
    /**
     * @param mixed[] $data
     * @return string
     */
    public function dump(array $data) : string
    {
        \array_walk_recursive($data, static function (&$val) {
            if (!$val instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement) {
                return;
            }
            $val = self::statementToEntity($val);
        });
        return "# generated by Nette\n\n" . \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::encode($data, \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::BLOCK);
    }
    private static function statementToEntity(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement $val) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity
    {
        \array_walk_recursive($val->arguments, static function (&$val) {
            if ($val instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement) {
                $val = self::statementToEntity($val);
            } elseif ($val instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference) {
                $val = '@' . $val->getValue();
            }
        });
        $entity = $val->getEntity();
        if ($entity instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference) {
            $entity = '@' . $entity->getValue();
        } elseif (\is_array($entity)) {
            if ($entity[0] instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement) {
                return new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity(\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Neon::CHAIN, [self::statementToEntity($entity[0]), new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity('::' . $entity[1], $val->arguments)]);
            } elseif ($entity[0] instanceof \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference) {
                $entity = '@' . $entity[0]->getValue() . '::' . $entity[1];
            } elseif (\is_string($entity[0])) {
                $entity = $entity[0] . '::' . $entity[1];
            }
        }
        return new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Neon\Entity($entity, $val->arguments);
    }
    private function createFileHelperByFile(string $file) : \PHPStan\File\FileHelper
    {
        $dir = \dirname($file);
        if (!isset($this->fileHelpers[$dir])) {
            $this->fileHelpers[$dir] = new \PHPStan\File\FileHelper($dir);
        }
        return $this->fileHelpers[$dir];
    }
}
