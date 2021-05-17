<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Util\Exception;

use InvalidArgumentException;
use PhpParser\Lexer;
use PhpParser\Node;
use function get_class;
use function sprintf;
class NoNodePosition extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function fromNode(\PhpParser\Node $node)
    {
        return new self(\sprintf('%s doesn\'t contain position. Your %s is not configured properly', \get_class($node), \PhpParser\Lexer::class));
    }
}
