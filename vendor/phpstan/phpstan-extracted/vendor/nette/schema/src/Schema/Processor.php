<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema;

use RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Schema validator.
 */
final class Processor
{
    use Nette\SmartObject;
    /** @var array */
    public $onNewContext = [];
    /** @var bool */
    private $skipDefaults;
    public function skipDefaults(bool $value = \true)
    {
        $this->skipDefaults = $value;
    }
    /**
     * Normalizes and validates data. Result is a clean completed data.
     * @return mixed
     * @throws ValidationException
     */
    public function process(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema $schema, $data)
    {
        $context = $this->createContext();
        $data = $schema->normalize($data, $context);
        $this->throwsErrors($context);
        $data = $schema->complete($data, $context);
        $this->throwsErrors($context);
        return $data;
    }
    /**
     * Normalizes and validates and merges multiple data. Result is a clean completed data.
     * @return mixed
     * @throws ValidationException
     */
    public function processMultiple(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\Schema $schema, array $dataset)
    {
        $context = $this->createContext();
        $flatten = null;
        $first = \true;
        foreach ($dataset as $data) {
            $data = $schema->normalize($data, $context);
            $this->throwsErrors($context);
            $flatten = $first ? $data : $schema->merge($data, $flatten);
            $first = \false;
        }
        $data = $schema->complete($flatten, $context);
        $this->throwsErrors($context);
        return $data;
    }
    /**
     * @return void
     */
    private function throwsErrors(\RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context $context)
    {
        $messages = [];
        foreach ($context->errors as $error) {
            $pathStr = " '" . \implode(' › ', $error->path) . "'";
            $messages[] = \str_replace(' %path%', $error->path ? $pathStr : '', $error->message);
        }
        if ($messages) {
            throw new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\ValidationException($messages[0], $messages);
        }
    }
    private function createContext() : \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context
    {
        $context = new \RectorPrefix20210522\_HumbugBox0b2f2d5c77b8\Nette\Schema\Context();
        $context->skipDefaults = $this->skipDefaults;
        $this->onNewContext($context);
        return $context;
    }
}
