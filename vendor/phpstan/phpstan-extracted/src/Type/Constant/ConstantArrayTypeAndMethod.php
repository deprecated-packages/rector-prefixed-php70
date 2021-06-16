<?php

declare (strict_types=1);
namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
/** @api */
class ConstantArrayTypeAndMethod
{
    /** @var \PHPStan\Type\Type|null */
    private $type;
    /** @var string|null */
    private $method;
    /** @var TrinaryLogic */
    private $certainty;
    /**
     * @param \PHPStan\Type\Type|null $type
     * @param string|null $method
     */
    private function __construct($type, $method, \PHPStan\TrinaryLogic $certainty)
    {
        $this->type = $type;
        $this->method = $method;
        $this->certainty = $certainty;
    }
    /**
     * @return $this
     */
    public static function createConcrete(\PHPStan\Type\Type $type, string $method, \PHPStan\TrinaryLogic $certainty)
    {
        if ($certainty->no()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return new self($type, $method, $certainty);
    }
    /**
     * @return $this
     */
    public static function createUnknown()
    {
        return new self(null, null, \PHPStan\TrinaryLogic::createMaybe());
    }
    public function isUnknown() : bool
    {
        return $this->type === null;
    }
    public function getType() : \PHPStan\Type\Type
    {
        if ($this->type === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return $this->type;
    }
    public function getMethod() : string
    {
        if ($this->method === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return $this->method;
    }
    public function getCertainty() : \PHPStan\TrinaryLogic
    {
        return $this->certainty;
    }
}
