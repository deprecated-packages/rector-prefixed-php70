<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
final class AllAssignNodePropertyTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer $assignToPropertyTypeInferer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function inferProperty(\PhpParser\Node\Stmt\Property $property)
    {
        $classLike = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            // anonymous class possibly?
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($propertyName, $classLike);
    }
    public function getPriority() : int
    {
        return 1500;
    }
}
