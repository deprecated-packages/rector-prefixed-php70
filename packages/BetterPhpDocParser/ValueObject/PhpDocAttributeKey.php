<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use RectorPrefix20210620\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey as NativePhpDocAttributeKey;
final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    const START_AND_END = 'start_and_end';
    /**
     * @var string
     */
    const PARENT = \RectorPrefix20210620\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT;
    /**
     * @var string
     */
    const LAST_PHP_DOC_TOKEN_POSITION = 'last_token_position';
    /**
     * @var string
     */
    const ORIG_NODE = \RectorPrefix20210620\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE;
}
