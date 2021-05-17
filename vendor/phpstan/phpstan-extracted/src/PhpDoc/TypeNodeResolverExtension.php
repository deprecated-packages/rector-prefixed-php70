<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
interface TypeNodeResolverExtension
{
    const EXTENSION_TAG = 'phpstan.phpDoc.typeNodeResolverExtension';
    /**
     * @return \PHPStan\Type\Type|null
     */
    public function resolve(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PHPStan\Analyser\NameScope $nameScope);
}
