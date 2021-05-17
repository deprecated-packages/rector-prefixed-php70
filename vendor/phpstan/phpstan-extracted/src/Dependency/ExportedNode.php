<?php

declare (strict_types=1);
namespace PHPStan\Dependency;

interface ExportedNode
{
    /**
     * @param $this $node
     */
    public function equals($node) : bool;
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties);
    /**
     * @param mixed[] $data
     * @return self
     */
    public static function decode(array $data);
}
