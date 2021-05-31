<?php

declare (strict_types=1);
namespace Rector\Naming;

use Rector\Renaming\ValueObject\RenamedNamespace;
final class NamespaceMatcher
{
    /**
     * @param string[] $oldToNewNamespace
     * @return \Rector\Renaming\ValueObject\RenamedNamespace|null
     */
    public function matchRenamedNamespace(string $name, array $oldToNewNamespace)
    {
        \krsort($oldToNewNamespace);
        /** @var string $oldNamespace */
        foreach ($oldToNewNamespace as $oldNamespace => $newNamespace) {
            if (\strncmp($name, $oldNamespace, \strlen($oldNamespace)) === 0) {
                return new \Rector\Renaming\ValueObject\RenamedNamespace($name, $oldNamespace, $newNamespace);
            }
        }
        return null;
    }
}
