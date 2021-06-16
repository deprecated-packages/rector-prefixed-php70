<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

/** @api */
interface TypeNodeResolverAwareExtension
{
    /**
     * @return void
     */
    public function setTypeNodeResolver(\PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver);
}
