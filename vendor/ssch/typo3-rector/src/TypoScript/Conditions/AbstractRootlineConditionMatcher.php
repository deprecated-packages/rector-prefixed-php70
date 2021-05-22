<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\TypoScript\Conditions;

use RectorPrefix20210522\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
abstract class AbstractRootlineConditionMatcher implements \Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @return string|null
     */
    public function change(string $condition)
    {
        \preg_match('#' . $this->getType() . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(.*)#', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $values = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $matches[1], \true);
        $newConditions = [];
        foreach ($values as $value) {
            $newConditions[] = \sprintf('%s in %s', $value, $this->getExpression());
        }
        return \implode(' || ', $newConditions);
    }
    public function shouldApply(string $condition) : bool
    {
        if (\RectorPrefix20210522\Nette\Utils\Strings::contains($condition, self::CONTAINS_CONSTANT)) {
            return \false;
        }
        return \RectorPrefix20210522\Nette\Utils\Strings::startsWith($condition, $this->getType());
    }
    protected abstract function getType() : string;
    protected abstract function getExpression() : string;
}
