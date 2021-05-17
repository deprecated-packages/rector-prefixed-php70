<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
class VariableTypeHolder
{
    /** @var \PHPStan\Type\Type */
    private $type;
    /** @var \PHPStan\TrinaryLogic */
    private $certainty;
    public function __construct(\PHPStan\Type\Type $type, \PHPStan\TrinaryLogic $certainty)
    {
        $this->type = $type;
        $this->certainty = $certainty;
    }
    /**
     * @return $this
     */
    public static function createYes(\PHPStan\Type\Type $type)
    {
        return new self($type, \PHPStan\TrinaryLogic::createYes());
    }
    /**
     * @return $this
     */
    public static function createMaybe(\PHPStan\Type\Type $type)
    {
        return new self($type, \PHPStan\TrinaryLogic::createMaybe());
    }
    /**
     * @param $this $other
     */
    public function equals($other) : bool
    {
        if (!$this->certainty->equals($other->certainty)) {
            return \false;
        }
        return $this->type->equals($other->type);
    }
    /**
     * @param $this $other
     * @return $this
     */
    public function and($other)
    {
        if ($this->getType()->equals($other->getType())) {
            $type = $this->getType();
        } else {
            $type = \PHPStan\Type\TypeCombinator::union($this->getType(), $other->getType());
        }
        return new self($type, $this->getCertainty()->and($other->getCertainty()));
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    public function getCertainty() : \PHPStan\TrinaryLogic
    {
        return $this->certainty;
    }
}
