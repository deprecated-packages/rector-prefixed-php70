<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions;

use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;
use RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect;
/**
 * Decorators for services.
 */
final class DecoratorExtension extends \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\CompilerExtension
{
    public function getConfigSchema() : \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema
    {
        return \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::arrayOf(\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::structure(['setup' => \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::list(), 'tags' => \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::array(), 'inject' => \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Schema\Expect::bool()]));
    }
    public function beforeCompile()
    {
        $this->getContainerBuilder()->resolve();
        foreach ($this->config as $type => $info) {
            if ($info->inject !== null) {
                $info->tags[\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Extensions\InjectExtension::TAG_INJECT] = $info->inject;
            }
            $this->addSetups($type, \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($info->setup));
            $this->addTags($type, \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Helpers::filterArguments($info->tags));
        }
    }
    /**
     * @return void
     */
    public function addSetups(string $type, array $setups)
    {
        foreach ($this->findByType($type) as $def) {
            if ($def instanceof \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition) {
                $def = $def->getResultDefinition();
            }
            foreach ($setups as $setup) {
                if (\is_array($setup)) {
                    $setup = new \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Statement(\key($setup), \array_values($setup));
                }
                $def->addSetup($setup);
            }
        }
    }
    /**
     * @return void
     */
    public function addTags(string $type, array $tags)
    {
        $tags = \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\Utils\Arrays::normalize($tags, \true);
        foreach ($this->findByType($type) as $def) {
            $def->setTags($def->getTags() + $tags);
        }
    }
    private function findByType(string $type) : array
    {
        return \array_filter($this->getContainerBuilder()->getDefinitions(), function (\RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition $def) use($type) : bool {
            return \is_a($def->getType(), $type, \true) || $def instanceof \RectorPrefix20210520\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\FactoryDefinition && \is_a($def->getResultType(), $type, \true);
        });
    }
}
