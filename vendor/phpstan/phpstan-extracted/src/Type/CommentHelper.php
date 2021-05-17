<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PhpParser\Node;
/** @deprecated */
class CommentHelper
{
    /**
     * @return string|null
     */
    public static function getDocComment(\PhpParser\Node $node)
    {
        $phpDoc = $node->getDocComment();
        if ($phpDoc !== null) {
            return $phpDoc->getText();
        }
        return null;
    }
}
