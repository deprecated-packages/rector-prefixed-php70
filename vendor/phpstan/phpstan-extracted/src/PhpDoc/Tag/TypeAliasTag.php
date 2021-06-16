<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
/** @api */
class TypeAliasTag
{
    /** @var string */
    private $aliasName;
    /** @var TypeNode */
    private $typeNode;
    /** @var NameScope */
    private $nameScope;
    public function __construct(string $aliasName, \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PHPStan\Analyser\NameScope $nameScope)
    {
        $this->aliasName = $aliasName;
        $this->typeNode = $typeNode;
        $this->nameScope = $nameScope;
    }
    public function getAliasName() : string
    {
        return $this->aliasName;
    }
    public function getTypeAlias() : \PHPStan\Type\TypeAlias
    {
        return new \PHPStan\Type\TypeAlias($this->typeNode, $this->nameScope);
    }
}
