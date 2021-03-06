<?php

declare (strict_types=1);
namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\BoolPropertyConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;
final class BoolPropertyRenamer
{
    /**
     * @var \Rector\Naming\Guard\PropertyConflictingNameGuard\BoolPropertyConflictingNameGuard
     */
    private $boolPropertyConflictingNameGuard;
    /**
     * @var \Rector\Naming\PropertyRenamer\PropertyRenamer
     */
    private $propertyRenamer;
    public function __construct(\Rector\Naming\Guard\PropertyConflictingNameGuard\BoolPropertyConflictingNameGuard $boolPropertyConflictingNameGuard, \Rector\Naming\PropertyRenamer\PropertyRenamer $propertyRenamer)
    {
        $this->boolPropertyConflictingNameGuard = $boolPropertyConflictingNameGuard;
        $this->propertyRenamer = $propertyRenamer;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|null
     */
    public function rename(\Rector\Naming\ValueObject\PropertyRename $propertyRename)
    {
        if ($this->boolPropertyConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }
        return $this->propertyRenamer->rename($propertyRename);
    }
}
