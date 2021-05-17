<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Constant;

use PHPStan\Reflection\GlobalConstantReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
class RuntimeConstantReflection implements \PHPStan\Reflection\GlobalConstantReflection
{
    /** @var string */
    private $name;
    /** @var Type */
    private $valueType;
    /** @var string|null */
    private $fileName;
    /**
     * @param string|null $fileName
     */
    public function __construct(string $name, \PHPStan\Type\Type $valueType, $fileName)
    {
        $this->name = $name;
        $this->valueType = $valueType;
        $this->fileName = $fileName;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getValueType() : \PHPStan\Type\Type
    {
        return $this->valueType;
    }
    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    public function isDeprecated() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
    /**
     * @return string|null
     */
    public function getDeprecatedDescription()
    {
        return null;
    }
    public function isInternal() : \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }
}
