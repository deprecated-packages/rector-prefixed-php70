<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
final class VariableWithType
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var \PHPStan\Type\Type
     */
    private $type;
    private $phpParserTypeNode;
    /**
     * @param Identifier|Name|NullableType|UnionType|null $phpParserTypeNode
     */
    public function __construct(string $name, \PHPStan\Type\Type $type, $phpParserTypeNode)
    {
        $this->name = $name;
        $this->type = $type;
        $this->phpParserTypeNode = $phpParserTypeNode;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    /**
     * @return \PhpParser\Node|null
     */
    public function getPhpParserTypeNode()
    {
        return $this->phpParserTypeNode;
    }
}
