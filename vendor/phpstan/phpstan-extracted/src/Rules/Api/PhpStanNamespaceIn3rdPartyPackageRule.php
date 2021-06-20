<?php

declare (strict_types=1);
namespace PHPStan\Rules\Api;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Json;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\File\FileReader;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * @implements Rule<Node\Stmt\Namespace_>
 */
class PhpStanNamespaceIn3rdPartyPackageRule implements \PHPStan\Rules\Rule
{
    /** @var ApiRuleHelper */
    private $apiRuleHelper;
    public function __construct(\PHPStan\Rules\Api\ApiRuleHelper $apiRuleHelper)
    {
        $this->apiRuleHelper = $apiRuleHelper;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Stmt\Namespace_::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        $namespace = null;
        if ($node->name !== null) {
            $namespace = $node->name->toString();
        }
        if ($namespace === null || !$this->apiRuleHelper->isPhpStanName($namespace)) {
            return [];
        }
        $composerJson = $this->findComposerJsonContents(\dirname($scope->getFile()));
        if ($composerJson === null) {
            return [];
        }
        $packageName = $composerJson['name'] ?? null;
        if ($packageName !== null && \strpos($packageName, 'phpstan/') === 0) {
            return [];
        }
        return [\PHPStan\Rules\RuleErrorBuilder::message('Declaring PHPStan namespace is not allowed in 3rd party packages.')->tip("See:\n   https://phpstan.org/developing-extensions/backward-compatibility-promise")->build()];
    }
    /**
     * @return mixed[]|null
     */
    private function findComposerJsonContents(string $fromDirectory)
    {
        if (!\is_dir($fromDirectory)) {
            return null;
        }
        $composerJsonPath = $fromDirectory . '/composer.json';
        if (!\is_file($composerJsonPath)) {
            return $this->findComposerJsonContents(\dirname($fromDirectory));
        }
        try {
            return \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Json::decode(\PHPStan\File\FileReader::read($composerJsonPath), \RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\Json::FORCE_ARRAY);
        } catch (\RectorPrefix20210620\_HumbugBox15516bb2b566\Nette\Utils\JsonException $e) {
            return null;
        } catch (\PHPStan\File\CouldNotReadFileException $e) {
            return null;
        }
    }
}
