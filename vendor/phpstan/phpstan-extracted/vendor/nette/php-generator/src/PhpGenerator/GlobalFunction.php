<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette;
/**
 * Global function.
 *
 * @property string $body
 */
final class GlobalFunction
{
    use Nette\SmartObject;
    use Traits\FunctionLike;
    use Traits\NameAware;
    use Traits\CommentAware;
    use Traits\AttributeAware;
    /**
     * @return $this
     */
    public static function from(string $function)
    {
        return (new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromFunctionReflection(new \ReflectionFunction($function));
    }
    /**
     * @return $this
     */
    public static function withBodyFrom(string $function)
    {
        return (new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Factory())->fromFunctionReflection(new \ReflectionFunction($function), \true);
    }
    public function __toString() : string
    {
        try {
            return (new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Nette\PhpGenerator\Printer())->printFunction($this);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", \E_USER_ERROR);
            return '';
        }
    }
}
