<?php

declare (strict_types=1);
namespace PHPStan\Rules\PhpDoc;

use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
class UnresolvableTypeHelper
{
    /** @var bool */
    private $deepInspectTypes;
    public function __construct(bool $deepInspectTypes)
    {
        $this->deepInspectTypes = $deepInspectTypes;
    }
    public function containsUnresolvableType(\PHPStan\Type\Type $type) : bool
    {
        if ($this->deepInspectTypes) {
            $containsUnresolvable = \false;
            \PHPStan\Type\TypeTraverser::map($type, static function (\PHPStan\Type\Type $type, callable $traverse) use(&$containsUnresolvable) : Type {
                if ($type instanceof \PHPStan\Type\ErrorType) {
                    $containsUnresolvable = \true;
                    return $type;
                }
                if ($type instanceof \PHPStan\Type\NeverType && !$type->isExplicit()) {
                    $containsUnresolvable = \true;
                    return $type;
                }
                return $traverse($type);
            });
            return $containsUnresolvable;
        }
        if ($type instanceof \PHPStan\Type\ErrorType) {
            return \true;
        }
        return $type instanceof \PHPStan\Type\NeverType && !$type->isExplicit();
    }
}
