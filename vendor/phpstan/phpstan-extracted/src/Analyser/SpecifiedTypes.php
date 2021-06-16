<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Type\TypeCombinator;
class SpecifiedTypes
{
    /** @var mixed[] */
    private $sureTypes;
    /** @var mixed[] */
    private $sureNotTypes;
    /** @var bool */
    private $overwrite;
    /** @var array<string, ConditionalExpressionHolder[]> */
    private $newConditionalExpressionHolders;
    /**
     * @api
     * @param mixed[] $sureTypes
     * @param mixed[] $sureNotTypes
     * @param bool $overwrite
     * @param array<string, ConditionalExpressionHolder[]> $newConditionalExpressionHolders
     */
    public function __construct(array $sureTypes = [], array $sureNotTypes = [], bool $overwrite = \false, array $newConditionalExpressionHolders = [])
    {
        $this->sureTypes = $sureTypes;
        $this->sureNotTypes = $sureNotTypes;
        $this->overwrite = $overwrite;
        $this->newConditionalExpressionHolders = $newConditionalExpressionHolders;
    }
    /**
     * @api
     * @return mixed[]
     */
    public function getSureTypes() : array
    {
        return $this->sureTypes;
    }
    /**
     * @api
     * @return mixed[]
     */
    public function getSureNotTypes() : array
    {
        return $this->sureNotTypes;
    }
    public function shouldOverwrite() : bool
    {
        return $this->overwrite;
    }
    /**
     * @return array<string, ConditionalExpressionHolder[]>
     */
    public function getNewConditionalExpressionHolders() : array
    {
        return $this->newConditionalExpressionHolders;
    }
    /** @api
     * @return $this */
    public function intersectWith(\PHPStan\Analyser\SpecifiedTypes $other)
    {
        $sureTypeUnion = [];
        $sureNotTypeUnion = [];
        foreach ($this->sureTypes as $exprString => list($exprNode, $type)) {
            if (!isset($other->sureTypes[$exprString])) {
                continue;
            }
            $sureTypeUnion[$exprString] = [$exprNode, \PHPStan\Type\TypeCombinator::union($type, $other->sureTypes[$exprString][1])];
        }
        foreach ($this->sureNotTypes as $exprString => list($exprNode, $type)) {
            if (!isset($other->sureNotTypes[$exprString])) {
                continue;
            }
            $sureNotTypeUnion[$exprString] = [$exprNode, \PHPStan\Type\TypeCombinator::intersect($type, $other->sureNotTypes[$exprString][1])];
        }
        return new self($sureTypeUnion, $sureNotTypeUnion);
    }
    /** @api
     * @return $this */
    public function unionWith(\PHPStan\Analyser\SpecifiedTypes $other)
    {
        $sureTypeUnion = $this->sureTypes + $other->sureTypes;
        $sureNotTypeUnion = $this->sureNotTypes + $other->sureNotTypes;
        foreach ($this->sureTypes as $exprString => list($exprNode, $type)) {
            if (!isset($other->sureTypes[$exprString])) {
                continue;
            }
            $sureTypeUnion[$exprString] = [$exprNode, \PHPStan\Type\TypeCombinator::intersect($type, $other->sureTypes[$exprString][1])];
        }
        foreach ($this->sureNotTypes as $exprString => list($exprNode, $type)) {
            if (!isset($other->sureNotTypes[$exprString])) {
                continue;
            }
            $sureNotTypeUnion[$exprString] = [$exprNode, \PHPStan\Type\TypeCombinator::union($type, $other->sureNotTypes[$exprString][1])];
        }
        return new self($sureTypeUnion, $sureNotTypeUnion);
    }
}
