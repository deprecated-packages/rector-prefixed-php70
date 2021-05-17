<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

interface TypeSpecifierAwareExtension
{
    /**
     * @return void
     */
    public function setTypeSpecifier(\PHPStan\Analyser\TypeSpecifier $typeSpecifier);
}
