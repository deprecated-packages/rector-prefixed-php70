<?php

declare (strict_types=1);
namespace Rector\CakePHP\ValueObject;

use PHPStan\Type\ObjectType;
final class ModalToGetSet
{
    /**
     * @var string
     */
    private $getMethod;
    /**
     * @var string
     */
    private $setMethod;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $unprefixedMethod;
    /**
     * @var int
     */
    private $minimalSetterArgumentCount = 1;
    /**
     * @var string|null
     */
    private $firstArgumentType;
    /**
     * @param string|null $getMethod
     * @param string|null $setMethod
     * @param string|null $firstArgumentType
     */
    public function __construct(string $type, string $unprefixedMethod, $getMethod = null, $setMethod = null, int $minimalSetterArgumentCount = 1, $firstArgumentType = null)
    {
        $this->type = $type;
        $this->unprefixedMethod = $unprefixedMethod;
        $this->minimalSetterArgumentCount = $minimalSetterArgumentCount;
        $this->firstArgumentType = $firstArgumentType;
        $this->getMethod = $getMethod ?? 'get' . \ucfirst($unprefixedMethod);
        $this->setMethod = $setMethod ?? 'set' . \ucfirst($unprefixedMethod);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
    }
    public function getUnprefixedMethod() : string
    {
        return $this->unprefixedMethod;
    }
    public function getGetMethod() : string
    {
        return $this->getMethod;
    }
    public function getSetMethod() : string
    {
        return $this->setMethod;
    }
    public function getMinimalSetterArgumentCount() : int
    {
        return $this->minimalSetterArgumentCount;
    }
    /**
     * @return string|null
     */
    public function getFirstArgumentType()
    {
        return $this->firstArgumentType;
    }
}
