<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210620\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class LanguageConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    const TYPE = 'language';
    /**
     * @return string|null
     */
    public function change(string $condition)
    {
        \preg_match('#^' . self::TYPE . '\\s*=\\s*(?<value>.*)$#iUm', $condition, $matches);
        if (!\is_string($matches['value'])) {
            return $condition;
        }
        return \sprintf('siteLanguage("twoLetterIsoCode") == "%s"', \trim($matches['value']));
    }
    public function shouldApply(string $condition) : bool
    {
        return \RectorPrefix20210620\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
