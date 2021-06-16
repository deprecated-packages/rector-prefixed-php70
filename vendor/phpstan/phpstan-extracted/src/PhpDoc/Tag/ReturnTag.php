<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
/** @api */
class ReturnTag implements \PHPStan\PhpDoc\Tag\TypedTag
{
    /** @var \PHPStan\Type\Type */
    private $type;
    /** @var bool */
    private $isExplicit;
    public function __construct(\PHPStan\Type\Type $type, bool $isExplicit)
    {
        $this->type = $type;
        $this->isExplicit = $isExplicit;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    public function isExplicit() : bool
    {
        return $this->isExplicit;
    }
    /**
     * @param Type $type
     * @return self
     */
    public function withType(\PHPStan\Type\Type $type) : \PHPStan\PhpDoc\Tag\TypedTag
    {
        return new self($type, $this->isExplicit);
    }
    /**
     * @return $this
     */
    public function toImplicit()
    {
        return new self($this->type, \false);
    }
}
