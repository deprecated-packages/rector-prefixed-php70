<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

/**
 * @enum
 */
final class MatchKind
{
    /**
     * @var string
     */
    const NORMAL = 'normal';
    /**
     * @var string
     */
    const ASSIGN = 'assign';
    /**
     * @var string
     */
    const RETURN = 'return';
    /**
     * @var string
     */
    const THROW = 'throw';
}
