<?php

declare (strict_types=1);
namespace Rector\Php53\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector\ReplaceHttpServerVarsByServerRectorTest
 * @changelog https://blog.tigertech.net/posts/php-5-3-http-server-vars/
 */
final class ReplaceHttpServerVarsByServerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    const VARIABLE_RENAME_MAP = ['HTTP_SERVER_VARS' => '_SERVER', 'HTTP_GET_VARS' => '_GET', 'HTTP_POST_VARS' => '_POST', 'HTTP_POST_FILES' => '_FILES', 'HTTP_SESSION_VARS' => '_SESSION', 'HTTP_ENV_VARS' => '_ENV', 'HTTP_COOKIE_VARS' => '_COOKIE'];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename old $HTTP_* variable names to new replacements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$serverVars = $HTTP_SERVER_VARS;', '$serverVars = $_SERVER;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     * @return \PhpParser\Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach (self::VARIABLE_RENAME_MAP as $oldName => $newName) {
            if (!$this->isName($node, $oldName)) {
                continue;
            }
            $node->name = $newName;
            return $node;
        }
        return null;
    }
}
