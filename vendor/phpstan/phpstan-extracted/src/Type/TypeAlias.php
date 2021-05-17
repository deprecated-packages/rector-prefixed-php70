<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
class TypeAlias
{
    /** @var TypeNode */
    private $typeNode;
    /** @var NameScope */
    private $nameScope;
    /** @var Type|null */
    private $resolvedType = null;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PHPStan\Analyser\NameScope $nameScope)
    {
        $this->typeNode = $typeNode;
        $this->nameScope = $nameScope;
    }
    /**
     * @return $this
     */
    public static function invalid()
    {
        $self = new self(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('*ERROR*'), new \PHPStan\Analyser\NameScope(null, []));
        $self->resolvedType = new \PHPStan\Type\ErrorType();
        return $self;
    }
    public function resolve(\PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver) : \PHPStan\Type\Type
    {
        if ($this->resolvedType === null) {
            $this->resolvedType = $typeNodeResolver->resolve($this->typeNode, $this->nameScope);
        }
        return $this->resolvedType;
    }
}
