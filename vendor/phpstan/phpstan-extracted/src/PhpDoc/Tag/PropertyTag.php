<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
/** @api */
class PropertyTag
{
    /** @var \PHPStan\Type\Type */
    private $type;
    /** @var bool */
    private $readable;
    /** @var bool */
    private $writable;
    public function __construct(\PHPStan\Type\Type $type, bool $readable, bool $writable)
    {
        $this->type = $type;
        $this->readable = $readable;
        $this->writable = $writable;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    public function isReadable() : bool
    {
        return $this->readable;
    }
    public function isWritable() : bool
    {
        return $this->writable;
    }
}
