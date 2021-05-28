<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210528\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class CompatVersionConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    const TYPE = 'compatVersion';
    /**
     * @return string|null
     */
    public function change(string $condition)
    {
        \preg_match('#' . self::TYPE . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<value>.*)$#iUm', $condition, $matches);
        if (!\is_string($matches['value'])) {
            return $condition;
        }
        return \sprintf('compatVersion("%s")', \trim($matches['value']));
    }
    public function shouldApply(string $condition) : bool
    {
        return \RectorPrefix20210528\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
