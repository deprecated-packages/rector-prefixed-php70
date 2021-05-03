<?php

declare (strict_types=1);
namespace RectorPrefix20210503;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20210503\Tracy\Dumper;
/**
 * @return void
 */
function dump_node(\PhpParser\Node $node, int $depth = 2)
{
    \RectorPrefix20210503\Tracy\Dumper::dump($node, [\RectorPrefix20210503\Tracy\Dumper::DEPTH => $depth]);
}
/**
 * @param Node|Node[] $node
 * @return void
 */
function print_node($node)
{
    $standard = new \PhpParser\PrettyPrinter\Standard();
    if (\is_array($node)) {
        foreach ($node as $singleNode) {
            $printedContent = $standard->prettyPrint([$singleNode]);
            \RectorPrefix20210503\Tracy\Dumper::dump($printedContent);
        }
    } else {
        $printedContent = $standard->prettyPrint([$node]);
        \RectorPrefix20210503\Tracy\Dumper::dump($printedContent);
    }
}
