<?php

declare (strict_types=1);
namespace RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\PackageVersions;

use RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions;
use OutOfBoundsException;
\class_exists(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::class);
/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'phpstan/phpstan-src';
    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS = array('clue/block-react' => 'v1.4.0@c8e7583ae55127b89d6915480ce295bac81c4f88', 'clue/ndjson-react' => 'v1.1.0@767ec9543945802b5766fab0da4520bf20626f66', 'composer/ca-bundle' => '1.2.8@8a7ecad675253e4654ea05505233285377405215', 'composer/package-versions-deprecated' => '1.11.99@c8c9aa8a14cc3d3bec86d0a8c3fa52ea79936855', 'composer/xdebug-handler' => '1.4.4@6e076a124f7ee146f2487554a94b6a19a74887ba', 'evenement/evenement' => 'v3.0.1@531bfb9d15f8aa57454f5f0285b18bec903b8fb7', 'hoa/compiler' => '3.17.08.08@aa09caf0bf28adae6654ca6ee415ee2f522672de', 'hoa/consistency' => '1.17.05.02@fd7d0adc82410507f332516faf655b6ed22e4c2f', 'hoa/event' => '1.17.01.13@6c0060dced212ffa3af0e34bb46624f990b29c54', 'hoa/exception' => '1.17.01.16@091727d46420a3d7468ef0595651488bfc3a458f', 'hoa/file' => '1.17.07.11@35cb979b779bc54918d2f9a4e02ed6c7a1fa67ca', 'hoa/iterator' => '2.17.01.10@d1120ba09cb4ccd049c86d10058ab94af245f0cc', 'hoa/math' => '1.17.05.16@7150785d30f5d565704912116a462e9f5bc83a0c', 'hoa/protocol' => '1.17.01.14@5c2cf972151c45f373230da170ea015deecf19e2', 'hoa/regex' => '1.17.01.13@7e263a61b6fb45c1d03d8e5ef77668518abd5bec', 'hoa/stream' => '1.17.02.21@3293cfffca2de10525df51436adf88a559151d82', 'hoa/ustring' => '4.17.01.16@e6326e2739178799b1fe3fdd92029f9517fa17a0', 'hoa/visitor' => '2.17.01.16@c18fe1cbac98ae449e0d56e87469103ba08f224a', 'hoa/zformat' => '1.17.01.10@522c381a2a075d4b9dbb42eb4592dd09520e4ac2', 'jean85/pretty-package-versions' => '1.5.1@a917488320c20057da87f67d0d40543dd9427f7a', 'jetbrains/phpstorm-stubs' => 'dev-master@0a73df114cdea7f30c8b5f6fbfbf8e6839a89e88', 'nette/bootstrap' => 'v3.0.2@67830a65b42abfb906f8e371512d336ebfb5da93', 'nette/di' => 'v3.0.5@766e8185196a97ded4f9128db6d79a3a124b7eb6', 'nette/finder' => 'v2.5.2@4ad2c298eb8c687dd0e74ae84206a4186eeaed50', 'nette/neon' => 'v3.2.1@a5b3a60833d2ef55283a82d0c30b45d136b29e75', 'nette/php-generator' => 'v3.5.0@9162f7455059755dcbece1b5570d1bbfc6f0ab0d', 'nette/robot-loader' => 'v3.3.1@15c1ecd0e6e69e8d908dfc4cca7b14f3b850a96b', 'nette/schema' => 'v1.0.2@febf71fb4052c824046f5a33f4f769a6e7fa0cb4', 'nette/utils' => 'v3.1.3@c09937fbb24987b2a41c6022ebe84f4f1b8eec0f', 'nikic/php-parser' => 'v4.10.5@4432ba399e47c66624bc73c8c0f811e5c109576f', 'ondram/ci-detector' => '3.5.1@594e61252843b68998bddd48078c5058fe9028bd', 'ondrejmirtes/better-reflection' => '4.3.57@37beae8f792c0bc1c1bf616f19a52a966d29e001', 'phpstan/php-8-stubs' => '0.1.17@8735d01708c19384dcb81137e22998a5b40bc247', 'phpstan/phpdoc-parser' => '0.5.4@e352d065af1ae9b41c12d1dfd309e90f7b1f55c9', 'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf', 'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363', 'psr/log' => '1.1.3@0f73288fd15629204f9d42b7055f72dacbe811fc', 'react/cache' => 'v1.1.0@44a568925556b0bd8cacc7b49fb0f1cf0d706a0c', 'react/child-process' => 'v0.6.1@6895afa583d51dc10a4b9e93cd3bce17b3b77ac3', 'react/dns' => 'v1.4.0@665260757171e2ab17485b44e7ffffa7acb6ca1f', 'react/event-loop' => 'v1.1.1@6d24de090cd59cfc830263cfba965be77b563c13', 'react/http' => 'v1.1.0@754b0c18545d258922ffa907f3b18598280fdecd', 'react/promise' => 'v2.8.0@f3cff96a19736714524ca0dd1d4130de73dbbbc4', 'react/promise-stream' => 'v1.2.0@6384d8b76cf7dcc44b0bf3343fb2b2928412d1fe', 'react/promise-timer' => 'v1.6.0@daee9baf6ef30c43ea4c86399f828bb5f558f6e6', 'react/socket' => 'v1.6.0@e2b96b23a13ca9b41ab343268dbce3f8ef4d524a', 'react/stream' => 'v1.1.1@7c02b510ee3f582c810aeccd3a197b9c2f52ff1a', 'ringcentral/psr7' => '1.3.0@360faaec4b563958b673fb52bbe94e37f14bc686', 'roave/signature' => '1.1.0@c4e8a59946bad694ab5682a76e7884a9157a8a2c', 'symfony/console' => 'v4.4.21@1ba4560dbbb9fcf5ae28b61f71f49c678086cf23', 'symfony/finder' => 'v4.4.16@26f63b8d4e92f2eecd90f6791a563ebb001abe31', 'symfony/polyfill-mbstring' => 'v1.22.1@5232de97ee3b75b0360528dae24e73db49566ab1', 'symfony/polyfill-php73' => 'v1.22.1@a678b42e92f86eca04b7fa4c0f6f19d097fb69e2', 'symfony/polyfill-php80' => 'v1.22.1@dc3063ba22c2a1fd2f45ed856374d79114998f91', 'symfony/service-contracts' => 'v1.1.8@ffc7f5692092df31515df2a5ecf3b7302b3ddacf', 'brianium/paratest' => 'v6.2.0@9a94366983ce32c7724fc92e3b544327d4adb9be', 'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b', 'drupol/phposinfo' => '1.6.5@36b0250d38279c8a131a1898a31e359606024507', 'myclabs/deep-copy' => '1.10.2@776f831124e9c62e1a2c601ecc52e776d8bb7220', 'nategood/httpful' => '0.2.20@c1cd4d46a4b281229032cf39d4dd852f9887c0f6', 'phar-io/manifest' => '2.0.1@85265efd3af7ba3ca4b2a2c34dbfc5788dd29133', 'phar-io/version' => '3.1.0@bae7c545bef187884426f042434e561ab1ddb182', 'php-parallel-lint/php-parallel-lint' => 'v1.2.0@474f18bc6cc6aca61ca40bfab55139de614e51ca', 'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b', 'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556', 'phpdocumentor/type-resolver' => '1.4.0@6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0', 'phpspec/prophecy' => '1.13.0@be1996ed8adc35c3fd795488a653f4b518be70ea', 'phpstan/phpstan-deprecation-rules' => '0.12.5@bfabc6a1b4617fbcbff43f03a4c04eae9bafae21', 'phpstan/phpstan-nette' => 'dev-master@9feb3cafff0f1d3bba38f0e8680085089deceb62', 'phpstan/phpstan-php-parser' => '0.12.2@0b27eec1e92d48fa82199844dec119b1b22baba0', 'phpstan/phpstan-phpunit' => 'dev-master@52f7072ddc5f81492f9d2de65a24813a48c90b18', 'phpstan/phpstan-strict-rules' => '0.12.9@0705fefc7c9168529fd130e341428f5f10f4f01d', 'phpunit/php-code-coverage' => '9.2.6@f6293e1b30a2354e8428e004689671b83871edde', 'phpunit/php-file-iterator' => '3.0.5@aa4be8575f26070b100fccb67faabb28f21f66f8', 'phpunit/php-invoker' => '3.1.1@5a10147d0aaf65b58940a0b72f71c9ac0423cc67', 'phpunit/php-text-template' => '2.0.4@5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28', 'phpunit/php-timer' => '5.0.3@5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2', 'phpunit/phpunit' => '9.5.4@c73c6737305e779771147af66c96ca6a7ed8a741', 'sebastian/cli-parser' => '1.0.1@442e7c7e687e42adc03470c7b668bc4b2402c0b2', 'sebastian/code-unit' => '1.0.8@1fc9f64c0927627ef78ba436c9b17d967e68e120', 'sebastian/code-unit-reverse-lookup' => '2.0.3@ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5', 'sebastian/comparator' => '4.0.6@55f4261989e546dc112258c7a75935a81a7ce382', 'sebastian/complexity' => '2.0.2@739b35e53379900cc9ac327b2147867b8b6efd88', 'sebastian/diff' => '4.0.4@3461e3fccc7cfdfc2720be910d3bd73c69be590d', 'sebastian/environment' => '5.1.3@388b6ced16caa751030f6a69e588299fa09200ac', 'sebastian/exporter' => '4.0.3@d89cc98761b8cb5a1a235a6b703ae50d34080e65', 'sebastian/global-state' => '5.0.2@a90ccbddffa067b51f574dea6eb25d5680839455', 'sebastian/lines-of-code' => '1.0.3@c1c2e997aa3146983ed888ad08b15470a2e22ecc', 'sebastian/object-enumerator' => '4.0.4@5c9eeac41b290a3712d88851518825ad78f45c71', 'sebastian/object-reflector' => '2.0.4@b4f479ebdbf63ac605d183ece17d8d7fe49c15c7', 'sebastian/recursion-context' => '4.0.4@cd9d8cf3c5804de4341c283ed787f099f5506172', 'sebastian/resource-operations' => '3.0.3@0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8', 'sebastian/type' => '2.3.1@81cd61ab7bbf2de744aba0ea61fae32f721df3d2', 'sebastian/version' => '3.0.2@c6c1022351a901512170118436c764e473f6de8c', 'seld/jsonlint' => '1.8.3@9ad6ce79c342fbd44df10ea95511a1b24dee5b57', 'symfony/polyfill-ctype' => 'v1.22.1@c6c942b1ac76c82448322025e084cadc56048b4e', 'symfony/process' => 'v5.2.4@313a38f09c77fbcdc1d223e57d368cea76a2fd2f', 'theseer/tokenizer' => '1.2.0@75a63c33a8577608444246075ea0af0d052e452a', 'vaimo/composer-patches' => '4.22.4@3da4cdf03fb4dc8d92b3d435de183f6044d679d6', 'vaimo/topological-sort' => '1.0.0@e19b93df2bac0e995ecd4b982ec4ea2fb1131e64', 'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25', 'phpstan/phpstan' => '0.12.86@ea1313b0fd43b5bc4089c9aac5aee8dc5056678a', 'phpstan/phpstan-src' => '0.12.86@ea1313b0fd43b5bc4089c9aac5aee8dc5056678a');
    private function __construct()
    {
    }
    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!\class_exists(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::class, \false) || !\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }
        return \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::getRootPackage()['name'];
    }
    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName) : string
    {
        if (\class_exists(\RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::class, \false) && \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::getRawData()) {
            return \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::getPrettyVersion($packageName) . '@' . \RectorPrefix20210531\_HumbugBox0b2f2d5c77b8\Composer\InstalledVersions::getReference($packageName);
        }
        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }
        throw new \OutOfBoundsException('Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files');
    }
}
