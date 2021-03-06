<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\DependencyInjection\PropertyAdder;
final class DependencyInjectionMethodCallAnalyzer
{
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver
     */
    private $serviceTypeMethodCallResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\PostRector\DependencyInjection\PropertyAdder
     */
    private $propertyAdder;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\PostRector\DependencyInjection\PropertyAdder $propertyAdder)
    {
        $this->propertyNaming = $propertyNaming;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyAdder = $propertyAdder;
    }
    /**
     * @return \PhpParser\Node|null
     */
    public function replaceMethodCallWithPropertyFetchAndDependency(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $serviceType = $this->serviceTypeMethodCallResolver->resolve($methodCall);
        if (!$serviceType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $classLike = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
        $this->propertyAdder->addConstructorDependencyToClass($classLike, $serviceType, $propertyName);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
