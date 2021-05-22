<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\Composer;

use BadMethodCallException;
use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use RectorPrefix20210522\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/guide-installation/master/en-us/MigrateToComposer/MigrationSteps.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\Composer\RemoveCmsPackageDirFromExtraComposerRector\RemoveCmsPackageDirFromExtraComposerRectorTest
 */
final class RemoveCmsPackageDirFromExtraComposerRector implements \Rector\Composer\Contract\Rector\ComposerRectorInterface
{
    /**
     * @var string
     */
    const TYPO3_CMS = 'typo3/cms';
    /**
     * @return void
     */
    public function refactor(\RectorPrefix20210522\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson)
    {
        $extra = $composerJson->getExtra();
        if (!isset($extra[self::TYPO3_CMS])) {
            return;
        }
        if (!isset($extra[self::TYPO3_CMS]['cms-package-dir'])) {
            return;
        }
        unset($extra[self::TYPO3_CMS]['cms-package-dir']);
        $composerJson->setExtra($extra);
    }
    /**
     * @return void
     */
    public function configure(array $configuration)
    {
        throw new \BadMethodCallException('Not allowed. No configuration option available');
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change package name in `composer.json`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
    "extra": {
        "typo3/cms": {
            "cms-package-dir": "{$vendor-dir}/typo3/cms"
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "extra": {
        "typo3/cms": {
        }
    }
}
CODE_SAMPLE
, ['not_allowed' => 'not_available'])]);
    }
}
