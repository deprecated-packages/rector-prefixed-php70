<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
final class ThisTemplatePropertyFetchAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string|null
     */
    public function resolveTemplateParameterNameFromAssign(\PhpParser\Node\Expr\Assign $assign)
    {
        if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        $propertyFetch = $assign->var;
        if (!$this->isTemplatePropertyFetch($propertyFetch->var)) {
            return null;
        }
        return $this->nodeNameResolver->getName($propertyFetch);
    }
    /**
     * $this->template->someKey => "someKey"
     * @return string|null
     */
    public function matchThisTemplateKey(\PhpParser\Node\Expr $expr)
    {
        if (!$expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$expr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($expr->var, 'template')) {
            return null;
        }
        return $this->nodeNameResolver->getName($expr->name);
    }
    /**
     * Looks for: $this->template
     *
     * $template
     */
    public function isTemplatePropertyFetch(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if (!$expr->var instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->var, 'this')) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->name, 'template');
    }
}
