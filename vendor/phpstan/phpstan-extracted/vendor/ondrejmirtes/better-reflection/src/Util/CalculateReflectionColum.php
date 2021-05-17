<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util;

use PhpParser\Node;
use PHPStan\BetterReflection\Util\Exception\InvalidNodePosition;
use PHPStan\BetterReflection\Util\Exception\NoNodePosition;
use function strlen;
use function strrpos;
/**
 * @internal
 */
final class CalculateReflectionColum
{
    /**
     * @throws InvalidNodePosition
     * @throws NoNodePosition
     */
    public static function getStartColumn(string $source, \PhpParser\Node $node) : int
    {
        if (!$node->hasAttribute('startFilePos')) {
            throw \PHPStan\BetterReflection\Util\Exception\NoNodePosition::fromNode($node);
        }
        return self::calculateColumn($source, $node->getStartFilePos());
    }
    /**
     * @throws InvalidNodePosition
     * @throws NoNodePosition
     */
    public static function getEndColumn(string $source, \PhpParser\Node $node) : int
    {
        if (!$node->hasAttribute('endFilePos')) {
            throw \PHPStan\BetterReflection\Util\Exception\NoNodePosition::fromNode($node);
        }
        return self::calculateColumn($source, $node->getEndFilePos());
    }
    /**
     * @throws InvalidNodePosition
     */
    private static function calculateColumn(string $source, int $position) : int
    {
        $sourceLength = \strlen($source);
        if ($position > $sourceLength) {
            throw \PHPStan\BetterReflection\Util\Exception\InvalidNodePosition::fromPosition($position);
        }
        $lineStartPosition = \strrpos($source, "\n", $position - $sourceLength);
        if ($lineStartPosition === \false) {
            return $position + 1;
        }
        return $position - $lineStartPosition;
    }
}
