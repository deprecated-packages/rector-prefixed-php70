<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\TypoScript\Conditions;

use RectorPrefix20210523\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher;
final class VersionConditionMatcher implements \Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    const TYPE = 'version';
    /**
     * @return string|null
     */
    public function change(string $condition)
    {
        return null;
    }
    public function shouldApply(string $condition) : bool
    {
        return \RectorPrefix20210523\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
