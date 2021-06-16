<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ClassNameImportSkipper
{
    /**
     * @var mixed[]
     */
    private $classNameImportSkipVoters;
    /**
     * @var \Rector\PSR4\Collector\RenamedClassesCollector
     */
    private $renamedClassesCollector;
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(array $classNameImportSkipVoters, \Rector\PSR4\Collector\RenamedClassesCollector $renamedClassesCollector)
    {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
        $this->renamedClassesCollector = $renamedClassesCollector;
    }
    public function shouldSkipNameForFullyQualifiedObjectType(\PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($fullyQualifiedObjectType, $node)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Use_[] $existingUses
     */
    public function isShortNameInUseStatement(\PhpParser\Node\Name $name, array $existingUses) : bool
    {
        $longName = $name->toString();
        if (\strpos($longName, '\\') !== \false) {
            return \false;
        }
        return $this->isFoundInUse($name, $existingUses);
    }
    /**
     * @param Use_[] $uses
     */
    public function isAlreadyImported(\PhpParser\Node\Name $name, array $uses) : bool
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->toString() === $name->toString()) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param Use_[] $uses
     */
    public function isFoundInUse(\PhpParser\Node\Name $name, array $uses) : bool
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->getLast() !== $name->getLast()) {
                    continue;
                }
                if ($this->isJustRenamedClass($name, $useUse)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    private function isJustRenamedClass(\PhpParser\Node\Name $name, \PhpParser\Node\Stmt\UseUse $useUse) : bool
    {
        // is in renamed classes? skip it
        foreach ($this->renamedClassesCollector->getOldToNewClasses() as $oldClass => $newClass) {
            // is class being renamed in use imports?
            if ($name->toString() !== $newClass) {
                continue;
            }
            if ($useUse->name->toString() !== $oldClass) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
