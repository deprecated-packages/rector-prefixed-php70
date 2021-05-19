<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema;

use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\AnyOf;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Structure;
use RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type;
/**
 * Schema generator.
 *
 * @method static Type scalar($default = null)
 * @method static Type string($default = null)
 * @method static Type int($default = null)
 * @method static Type float($default = null)
 * @method static Type bool($default = null)
 * @method static Type null()
 * @method static Type array($default = [])
 * @method static Type list($default = [])
 * @method static Type mixed($default = null)
 * @method static Type email($default = null)
 */
final class Expect
{
    use Nette\SmartObject;
    public static function __callStatic(string $name, array $args) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type
    {
        $type = new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type($name);
        if ($args) {
            $type->default($args[0]);
        }
        return $type;
    }
    public static function type(string $type) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type
    {
        return new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type($type);
    }
    /**
     * @param  mixed|Schema  ...$set
     */
    public static function anyOf(...$set) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\AnyOf
    {
        return new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\AnyOf(...$set);
    }
    /**
     * @param  Schema[]  $items
     */
    public static function structure(array $items) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Structure
    {
        return new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Structure($items);
    }
    /**
     * @param  object  $object
     */
    public static function from($object, array $items = []) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Structure
    {
        $ro = new \ReflectionObject($object);
        foreach ($ro->getProperties() as $prop) {
            $type = \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Helpers::getPropertyType($prop) ?? 'mixed';
            $item =& $items[$prop->getName()];
            if (!$item) {
                $item = new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type($type);
                if (\PHP_VERSION_ID >= 70400 && !$prop->isInitialized($object)) {
                    $item->required();
                } else {
                    $def = $prop->getValue($object);
                    if (\is_object($def)) {
                        $item = static::from($def);
                    } elseif ($def === null && !\RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Utils\Validators::is(null, $type)) {
                        $item->required();
                    } else {
                        $item->default($def);
                    }
                }
            }
        }
        return (new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Structure($items))->castTo($ro->getName());
    }
    /**
     * @param  string|Schema  $type
     */
    public static function arrayOf($type) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type
    {
        return (new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type('array'))->items($type);
    }
    /**
     * @param  string|Schema  $type
     */
    public static function listOf($type) : \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type
    {
        return (new \RectorPrefix20210519\_HumbugBox0b2f2d5c77b8\Nette\Schema\Elements\Type('list'))->items($type);
    }
}
