<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210531\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class VersionConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
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
        return \RectorPrefix20210531\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
