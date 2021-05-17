<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Type\Type;
class ExpressionContext
{
    /** @var bool */
    private $isDeep;
    /** @var string|null */
    private $inAssignRightSideVariableName;
    /** @var Type|null */
    private $inAssignRightSideType;
    /**
     * @param string|null $inAssignRightSideVariableName
     * @param \PHPStan\Type\Type|null $inAssignRightSideType
     */
    private function __construct(bool $isDeep, $inAssignRightSideVariableName, $inAssignRightSideType)
    {
        $this->isDeep = $isDeep;
        $this->inAssignRightSideVariableName = $inAssignRightSideVariableName;
        $this->inAssignRightSideType = $inAssignRightSideType;
    }
    /**
     * @return $this
     */
    public static function createTopLevel()
    {
        return new self(\false, null, null);
    }
    /**
     * @return $this
     */
    public static function createDeep()
    {
        return new self(\true, null, null);
    }
    /**
     * @return $this
     */
    public function enterDeep()
    {
        if ($this->isDeep) {
            return $this;
        }
        return new self(\true, $this->inAssignRightSideVariableName, $this->inAssignRightSideType);
    }
    public function isDeep() : bool
    {
        return $this->isDeep;
    }
    /**
     * @return $this
     */
    public function enterRightSideAssign(string $variableName, \PHPStan\Type\Type $type)
    {
        return new self($this->isDeep, $variableName, $type);
    }
    /**
     * @return string|null
     */
    public function getInAssignRightSideVariableName()
    {
        return $this->inAssignRightSideVariableName;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function getInAssignRightSideType()
    {
        return $this->inAssignRightSideType;
    }
}
