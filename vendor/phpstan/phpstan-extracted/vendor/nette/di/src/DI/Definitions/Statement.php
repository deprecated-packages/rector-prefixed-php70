<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings;
/**
 * Assignment or calling statement.
 *
 * @property string|array|Definition|Reference|null $entity
 */
final class Statement implements \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\DynamicParameter
{
    use Nette\SmartObject;
    /** @var array */
    public $arguments;
    /** @var string|array|Definition|Reference|null */
    private $entity;
    /**
     * @param  string|array|Definition|Reference|null  $entity
     */
    public function __construct($entity, array $arguments = [])
    {
        if ($entity !== null && !\is_string($entity) && !$entity instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition && !$entity instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference && !(\is_array($entity) && \array_keys($entity) === [0, 1] && (\is_string($entity[0]) || $entity[0] instanceof self || $entity[0] instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference || $entity[0] instanceof \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Definition))) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\InvalidArgumentException('Argument is not valid Statement entity.');
        }
        // normalize Class::method to [Class, method]
        if (\is_string($entity) && \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::contains($entity, '::') && !\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Utils\Strings::contains($entity, '?')) {
            $entity = \explode('::', $entity);
        }
        if (\is_string($entity) && \substr($entity, 0, 1) === '@') {
            // normalize @service to Reference
            $entity = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\substr($entity, 1));
        } elseif (\is_array($entity) && \is_string($entity[0]) && \substr($entity[0], 0, 1) === '@') {
            $entity[0] = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Definitions\Reference(\substr($entity[0], 1));
        }
        $this->entity = $entity;
        $this->arguments = $arguments;
    }
    /** @return string|array|Definition|Reference|null */
    public function getEntity()
    {
        return $this->entity;
    }
}
\class_exists(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\DI\Statement::class);
