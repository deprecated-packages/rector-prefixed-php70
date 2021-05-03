<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use PHPStan\Type\ObjectType;
final class RenamedClassesDataCollector
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];
    /**
     * @param array<string, string> $oldToNewClasses
     * @return void
     */
    public function addOldToNewClasses(array $oldToNewClasses)
    {
        $this->oldToNewClasses = \array_merge($this->oldToNewClasses, $oldToNewClasses);
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses() : array
    {
        return $this->oldToNewClasses;
    }
    /**
     * @return \PHPStan\Type\ObjectType|null
     */
    public function matchClassName(\PHPStan\Type\ObjectType $objectType)
    {
        $className = $objectType->getClassName();
        $renamedClassName = $this->oldToNewClasses[$className] ?? null;
        if ($renamedClassName === null) {
            return null;
        }
        return new \PHPStan\Type\ObjectType($renamedClassName);
    }
}
