<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory;

use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\FailedToParseJson;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\InvalidProjectDirectory;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\MissingInstalledJson;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Psr0Mapping;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Psr4Mapping;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\PsrAutoloaderLocator;
use PHPStan\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function array_filter;
use function array_map;
use function array_merge;
use function array_merge_recursive;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_dir;
use function json_decode;
use function realpath;
final class MakeLocatorForInstalledJson
{
    public function __invoke(string $installationPath, \PHPStan\BetterReflection\SourceLocator\Ast\Locator $astLocator) : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
    {
        $realInstallationPath = (string) \realpath($installationPath);
        if (!\is_dir($realInstallationPath)) {
            throw \PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\InvalidProjectDirectory::atPath($installationPath);
        }
        $installedJsonPath = $realInstallationPath . '/vendor/composer/installed.json';
        if (!\file_exists($installedJsonPath)) {
            throw \PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\MissingInstalledJson::inProjectPath($installationPath);
        }
        /** @var array{packages: list<array>}|list<array>|null $installedJson */
        $installedJson = \json_decode((string) \file_get_contents($installedJsonPath), \true);
        if (!\is_array($installedJson)) {
            throw \PHPStan\BetterReflection\SourceLocator\Type\Composer\Factory\Exception\FailedToParseJson::inFile($installedJsonPath);
        }
        /** @var list<array{name: string, autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}}>|null $installed */
        $installed = $installedJson['packages'] ?? $installedJson;
        $classMapPaths = \array_merge([], ...\array_map(function (array $package) use($realInstallationPath) : array {
            return $this->prefixPaths($this->packageToClassMapPaths($package), $this->packagePrefixPath($realInstallationPath, $package));
        }, $installed));
        $classMapFiles = \array_filter($classMapPaths, 'is_file');
        $classMapDirectories = \array_filter($classMapPaths, 'is_dir');
        $filePaths = \array_merge([], ...\array_map(function (array $package) use($realInstallationPath) : array {
            return $this->prefixPaths($this->packageToFilePaths($package), $this->packagePrefixPath($realInstallationPath, $package));
        }, $installed));
        return new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator(\array_merge([new \PHPStan\BetterReflection\SourceLocator\Type\Composer\PsrAutoloaderLocator(\PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Psr4Mapping::fromArrayMappings(\array_merge_recursive([], ...\array_map(function (array $package) use($realInstallationPath) : array {
            return $this->prefixWithPackagePath($this->packageToPsr4AutoloadNamespaces($package), $realInstallationPath, $package);
        }, $installed))), $astLocator), new \PHPStan\BetterReflection\SourceLocator\Type\Composer\PsrAutoloaderLocator(\PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Psr0Mapping::fromArrayMappings(\array_merge_recursive([], ...\array_map(function (array $package) use($realInstallationPath) : array {
            return $this->prefixWithPackagePath($this->packageToPsr0AutoloadNamespaces($package), $realInstallationPath, $package);
        }, $installed))), $astLocator), new \PHPStan\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator($classMapDirectories, $astLocator)], ...\array_map(static function (string $file) use($astLocator) : array {
            return [new \PHPStan\BetterReflection\SourceLocator\Type\SingleFileSourceLocator($file, $astLocator)];
        }, \array_merge($classMapFiles, $filePaths))));
    }
    /**
     * @param array{autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}} $package
     *
     * @return array<string, array<int, string>>
     */
    private function packageToPsr4AutoloadNamespaces(array $package) : array
    {
        return \array_map(static function ($namespacePaths) : array {
            return (array) $namespacePaths;
        }, $package['autoload']['psr-4'] ?? []);
    }
    /**
     * @param array{autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}} $package
     *
     * @return array<string, array<int, string>>
     */
    private function packageToPsr0AutoloadNamespaces(array $package) : array
    {
        return \array_map(static function ($namespacePaths) : array {
            return (array) $namespacePaths;
        }, $package['autoload']['psr-0'] ?? []);
    }
    /**
     * @param array{autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}} $package
     *
     * @return array<int, string>
     */
    private function packageToClassMapPaths(array $package) : array
    {
        return $package['autoload']['classmap'] ?? [];
    }
    /**
     * @param array{autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}} $package
     *
     * @return array<int, string>
     */
    private function packageToFilePaths(array $package) : array
    {
        return $package['autoload']['files'] ?? [];
    }
    /**
     * @param array{name: string, autoload: array{classmap: array<int, string>, files: array<int, string>, psr-4: array<string, array<int, string>>, psr-0: array<string, array<int, string>>}} $package
     * @param array{name: string}                                                                                                                                                               $package
     */
    private function packagePrefixPath(string $trimmedInstallationPath, array $package) : string
    {
        return $trimmedInstallationPath . '/vendor/' . $package['name'] . '/';
    }
    /**
     * @param array<int|string, array<string>> $paths
     * @param array{name: string}              $package
     *
     * @return array<int|string, string|array<string>>
     */
    private function prefixWithPackagePath(array $paths, string $trimmedInstallationPath, array $package) : array
    {
        $prefix = $this->packagePrefixPath($trimmedInstallationPath, $package);
        return \array_map(function (array $paths) use($prefix) : array {
            return $this->prefixPaths($paths, $prefix);
        }, $paths);
    }
    /**
     * @param array<int|string, string> $paths
     *
     * @return array<int|string, string>
     */
    private function prefixPaths(array $paths, string $prefix) : array
    {
        return \array_map(static function (string $path) use($prefix) {
            return $prefix . $path;
        }, $paths);
    }
}
