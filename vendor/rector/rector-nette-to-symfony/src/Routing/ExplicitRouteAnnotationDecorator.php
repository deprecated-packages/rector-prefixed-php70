<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Routing;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
final class ExplicitRouteAnnotationDecorator
{
    /**
     * @var string
     */
    const HAS_ROUTE_ANNOTATION = 'has_route_annotation';
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return void
     */
    public function decorateClassMethodWithRouteAnnotation(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $spacelessPhpDocTagNode = new \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode('@\\Symfony\\Component\\Routing\\Annotation\\Route', $doctrineAnnotationTagValueNode);
        $phpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
        $classMethod->setAttribute(self::HAS_ROUTE_ANNOTATION, \true);
    }
}
