<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
class ExtendsTag
{
    /** @var \PHPStan\Type\Type */
    private $type;
    public function __construct(\PHPStan\Type\Type $type)
    {
        $this->type = $type;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
}
