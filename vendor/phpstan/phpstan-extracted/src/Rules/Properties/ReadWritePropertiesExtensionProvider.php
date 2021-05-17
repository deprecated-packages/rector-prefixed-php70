<?php

declare (strict_types=1);
namespace PHPStan\Rules\Properties;

interface ReadWritePropertiesExtensionProvider
{
    const EXTENSION_TAG = 'phpstan.properties.readWriteExtension';
    /**
     * @return ReadWritePropertiesExtension[]
     */
    public function getExtensions() : array;
}
