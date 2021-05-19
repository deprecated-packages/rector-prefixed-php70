<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\TypoScript\Conditions;

interface TyposcriptConditionMatcher
{
    /**
     * @var array<string, string>
     */
    const OPERATOR_MAPPING = ['=' => '==', '>=' => '>=', '<=' => '<=', '>' => '>', '<' => '<', '!=' => '!='];
    /**
     * @var string
     */
    const ALLOWED_OPERATORS_REGEX = '\\<\\=|\\>\\=|\\!\\=|\\=|\\>|\\<';
    /**
     * @var string
     */
    const ZERO_ONE_OR_MORE_WHITESPACES = '\\s*';
    /**
     * @var string
     */
    const CONTAINS_CONSTANT = '{$';
    /**
     * If we return null it means conditions can be removed
     * @return string|null
     */
    public function change(string $condition);
    public function shouldApply(string $condition) : bool;
}
