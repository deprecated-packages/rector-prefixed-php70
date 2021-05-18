<?php

namespace RectorPrefix20210518\Composer;

use RectorPrefix20210518\Composer\Autoload\ClassLoader;
use RectorPrefix20210518\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => '0.12.86', 'version' => '0.12.86.0', 'aliases' => array(), 'reference' => 'ea1313b0fd43b5bc4089c9aac5aee8dc5056678a', 'name' => 'phpstan/phpstan-src'), 'versions' => array('brianium/paratest' => array('pretty_version' => 'v6.2.0', 'version' => '6.2.0.0', 'aliases' => array(), 'reference' => '9a94366983ce32c7724fc92e3b544327d4adb9be'), 'clue/block-react' => array('pretty_version' => 'v1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => 'c8e7583ae55127b89d6915480ce295bac81c4f88'), 'clue/ndjson-react' => array('pretty_version' => 'v1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => '767ec9543945802b5766fab0da4520bf20626f66'), 'composer/ca-bundle' => array('pretty_version' => '1.2.8', 'version' => '1.2.8.0', 'aliases' => array(), 'reference' => '8a7ecad675253e4654ea05505233285377405215'), 'composer/package-versions-deprecated' => array('pretty_version' => '1.11.99', 'version' => '1.11.99.0', 'aliases' => array(), 'reference' => 'c8c9aa8a14cc3d3bec86d0a8c3fa52ea79936855'), 'composer/xdebug-handler' => array('pretty_version' => '1.4.4', 'version' => '1.4.4.0', 'aliases' => array(), 'reference' => '6e076a124f7ee146f2487554a94b6a19a74887ba'), 'doctrine/instantiator' => array('pretty_version' => '1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => 'd56bf6102915de5702778fe20f2de3b2fe570b5b'), 'drupol/phposinfo' => array('pretty_version' => '1.6.5', 'version' => '1.6.5.0', 'aliases' => array(), 'reference' => '36b0250d38279c8a131a1898a31e359606024507'), 'evenement/evenement' => array('pretty_version' => 'v3.0.1', 'version' => '3.0.1.0', 'aliases' => array(), 'reference' => '531bfb9d15f8aa57454f5f0285b18bec903b8fb7'), 'grogy/php-parallel-lint' => array('replaced' => array(0 => '*')), 'hoa/compiler' => array('pretty_version' => '3.17.08.08', 'version' => '3.17.08.08', 'aliases' => array(), 'reference' => 'aa09caf0bf28adae6654ca6ee415ee2f522672de'), 'hoa/consistency' => array('pretty_version' => '1.17.05.02', 'version' => '1.17.05.02', 'aliases' => array(), 'reference' => 'fd7d0adc82410507f332516faf655b6ed22e4c2f'), 'hoa/event' => array('pretty_version' => '1.17.01.13', 'version' => '1.17.01.13', 'aliases' => array(), 'reference' => '6c0060dced212ffa3af0e34bb46624f990b29c54'), 'hoa/exception' => array('pretty_version' => '1.17.01.16', 'version' => '1.17.01.16', 'aliases' => array(), 'reference' => '091727d46420a3d7468ef0595651488bfc3a458f'), 'hoa/file' => array('pretty_version' => '1.17.07.11', 'version' => '1.17.07.11', 'aliases' => array(), 'reference' => '35cb979b779bc54918d2f9a4e02ed6c7a1fa67ca'), 'hoa/iterator' => array('pretty_version' => '2.17.01.10', 'version' => '2.17.01.10', 'aliases' => array(), 'reference' => 'd1120ba09cb4ccd049c86d10058ab94af245f0cc'), 'hoa/math' => array('pretty_version' => '1.17.05.16', 'version' => '1.17.05.16', 'aliases' => array(), 'reference' => '7150785d30f5d565704912116a462e9f5bc83a0c'), 'hoa/protocol' => array('pretty_version' => '1.17.01.14', 'version' => '1.17.01.14', 'aliases' => array(), 'reference' => '5c2cf972151c45f373230da170ea015deecf19e2'), 'hoa/regex' => array('pretty_version' => '1.17.01.13', 'version' => '1.17.01.13', 'aliases' => array(), 'reference' => '7e263a61b6fb45c1d03d8e5ef77668518abd5bec'), 'hoa/stream' => array('pretty_version' => '1.17.02.21', 'version' => '1.17.02.21', 'aliases' => array(), 'reference' => '3293cfffca2de10525df51436adf88a559151d82'), 'hoa/ustring' => array('pretty_version' => '4.17.01.16', 'version' => '4.17.01.16', 'aliases' => array(), 'reference' => 'e6326e2739178799b1fe3fdd92029f9517fa17a0'), 'hoa/visitor' => array('pretty_version' => '2.17.01.16', 'version' => '2.17.01.16', 'aliases' => array(), 'reference' => 'c18fe1cbac98ae449e0d56e87469103ba08f224a'), 'hoa/zformat' => array('pretty_version' => '1.17.01.10', 'version' => '1.17.01.10', 'aliases' => array(), 'reference' => '522c381a2a075d4b9dbb42eb4592dd09520e4ac2'), 'jakub-onderka/php-parallel-lint' => array('replaced' => array(0 => '*')), 'jean85/pretty-package-versions' => array('pretty_version' => '1.5.1', 'version' => '1.5.1.0', 'aliases' => array(), 'reference' => 'a917488320c20057da87f67d0d40543dd9427f7a'), 'jetbrains/phpstorm-stubs' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '9999999-dev'), 'reference' => '0a73df114cdea7f30c8b5f6fbfbf8e6839a89e88'), 'myclabs/deep-copy' => array('pretty_version' => '1.10.2', 'version' => '1.10.2.0', 'aliases' => array(), 'reference' => '776f831124e9c62e1a2c601ecc52e776d8bb7220', 'replaced' => array(0 => '1.10.2')), 'nategood/httpful' => array('pretty_version' => '0.2.20', 'version' => '0.2.20.0', 'aliases' => array(), 'reference' => 'c1cd4d46a4b281229032cf39d4dd852f9887c0f6'), 'nette/bootstrap' => array('pretty_version' => 'v3.0.2', 'version' => '3.0.2.0', 'aliases' => array(), 'reference' => '67830a65b42abfb906f8e371512d336ebfb5da93'), 'nette/di' => array('pretty_version' => 'v3.0.5', 'version' => '3.0.5.0', 'aliases' => array(), 'reference' => '766e8185196a97ded4f9128db6d79a3a124b7eb6'), 'nette/finder' => array('pretty_version' => 'v2.5.2', 'version' => '2.5.2.0', 'aliases' => array(), 'reference' => '4ad2c298eb8c687dd0e74ae84206a4186eeaed50'), 'nette/neon' => array('pretty_version' => 'v3.2.1', 'version' => '3.2.1.0', 'aliases' => array(), 'reference' => 'a5b3a60833d2ef55283a82d0c30b45d136b29e75'), 'nette/php-generator' => array('pretty_version' => 'v3.5.0', 'version' => '3.5.0.0', 'aliases' => array(), 'reference' => '9162f7455059755dcbece1b5570d1bbfc6f0ab0d'), 'nette/robot-loader' => array('pretty_version' => 'v3.3.1', 'version' => '3.3.1.0', 'aliases' => array(), 'reference' => '15c1ecd0e6e69e8d908dfc4cca7b14f3b850a96b'), 'nette/schema' => array('pretty_version' => 'v1.0.2', 'version' => '1.0.2.0', 'aliases' => array(), 'reference' => 'febf71fb4052c824046f5a33f4f769a6e7fa0cb4'), 'nette/utils' => array('pretty_version' => 'v3.1.3', 'version' => '3.1.3.0', 'aliases' => array(), 'reference' => 'c09937fbb24987b2a41c6022ebe84f4f1b8eec0f'), 'nikic/php-parser' => array('pretty_version' => 'v4.10.5', 'version' => '4.10.5.0', 'aliases' => array(), 'reference' => '4432ba399e47c66624bc73c8c0f811e5c109576f'), 'ocramius/package-versions' => array('replaced' => array(0 => '1.11.99')), 'ondram/ci-detector' => array('pretty_version' => '3.5.1', 'version' => '3.5.1.0', 'aliases' => array(), 'reference' => '594e61252843b68998bddd48078c5058fe9028bd'), 'ondrejmirtes/better-reflection' => array('pretty_version' => '4.3.57', 'version' => '4.3.57.0', 'aliases' => array(), 'reference' => '37beae8f792c0bc1c1bf616f19a52a966d29e001'), 'phar-io/manifest' => array('pretty_version' => '2.0.1', 'version' => '2.0.1.0', 'aliases' => array(), 'reference' => '85265efd3af7ba3ca4b2a2c34dbfc5788dd29133'), 'phar-io/version' => array('pretty_version' => '3.1.0', 'version' => '3.1.0.0', 'aliases' => array(), 'reference' => 'bae7c545bef187884426f042434e561ab1ddb182'), 'php-parallel-lint/php-parallel-lint' => array('pretty_version' => 'v1.2.0', 'version' => '1.2.0.0', 'aliases' => array(), 'reference' => '474f18bc6cc6aca61ca40bfab55139de614e51ca'), 'phpdocumentor/reflection-common' => array('pretty_version' => '2.2.0', 'version' => '2.2.0.0', 'aliases' => array(), 'reference' => '1d01c49d4ed62f25aa84a747ad35d5a16924662b'), 'phpdocumentor/reflection-docblock' => array('pretty_version' => '5.2.2', 'version' => '5.2.2.0', 'aliases' => array(), 'reference' => '069a785b2141f5bcf49f3e353548dc1cce6df556'), 'phpdocumentor/type-resolver' => array('pretty_version' => '1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => '6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0'), 'phpspec/prophecy' => array('pretty_version' => '1.13.0', 'version' => '1.13.0.0', 'aliases' => array(), 'reference' => 'be1996ed8adc35c3fd795488a653f4b518be70ea'), 'phpstan/php-8-stubs' => array('pretty_version' => '0.1.17', 'version' => '0.1.17.0', 'aliases' => array(), 'reference' => '8735d01708c19384dcb81137e22998a5b40bc247'), 'phpstan/phpdoc-parser' => array('pretty_version' => '0.5.4', 'version' => '0.5.4.0', 'aliases' => array(), 'reference' => 'e352d065af1ae9b41c12d1dfd309e90f7b1f55c9'), 'phpstan/phpstan' => array('replaced' => array(0 => '0.12.86')), 'phpstan/phpstan-deprecation-rules' => array('pretty_version' => '0.12.5', 'version' => '0.12.5.0', 'aliases' => array(), 'reference' => 'bfabc6a1b4617fbcbff43f03a4c04eae9bafae21'), 'phpstan/phpstan-nette' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '0.12.x-dev'), 'reference' => '9feb3cafff0f1d3bba38f0e8680085089deceb62'), 'phpstan/phpstan-php-parser' => array('pretty_version' => '0.12.2', 'version' => '0.12.2.0', 'aliases' => array(), 'reference' => '0b27eec1e92d48fa82199844dec119b1b22baba0'), 'phpstan/phpstan-phpunit' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '0.12.x-dev'), 'reference' => '52f7072ddc5f81492f9d2de65a24813a48c90b18'), 'phpstan/phpstan-src' => array('pretty_version' => '0.12.86', 'version' => '0.12.86.0', 'aliases' => array(), 'reference' => 'ea1313b0fd43b5bc4089c9aac5aee8dc5056678a'), 'phpstan/phpstan-strict-rules' => array('pretty_version' => '0.12.9', 'version' => '0.12.9.0', 'aliases' => array(), 'reference' => '0705fefc7c9168529fd130e341428f5f10f4f01d'), 'phpunit/php-code-coverage' => array('pretty_version' => '9.2.6', 'version' => '9.2.6.0', 'aliases' => array(), 'reference' => 'f6293e1b30a2354e8428e004689671b83871edde'), 'phpunit/php-file-iterator' => array('pretty_version' => '3.0.5', 'version' => '3.0.5.0', 'aliases' => array(), 'reference' => 'aa4be8575f26070b100fccb67faabb28f21f66f8'), 'phpunit/php-invoker' => array('pretty_version' => '3.1.1', 'version' => '3.1.1.0', 'aliases' => array(), 'reference' => '5a10147d0aaf65b58940a0b72f71c9ac0423cc67'), 'phpunit/php-text-template' => array('pretty_version' => '2.0.4', 'version' => '2.0.4.0', 'aliases' => array(), 'reference' => '5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28'), 'phpunit/php-timer' => array('pretty_version' => '5.0.3', 'version' => '5.0.3.0', 'aliases' => array(), 'reference' => '5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2'), 'phpunit/phpunit' => array('pretty_version' => '9.5.4', 'version' => '9.5.4.0', 'aliases' => array(), 'reference' => 'c73c6737305e779771147af66c96ca6a7ed8a741'), 'psr/container' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf'), 'psr/http-message' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => 'f6561bf28d520154e4b0ec72be95418abe6d9363'), 'psr/http-message-implementation' => array('provided' => array(0 => '1.0')), 'psr/log' => array('pretty_version' => '1.1.3', 'version' => '1.1.3.0', 'aliases' => array(), 'reference' => '0f73288fd15629204f9d42b7055f72dacbe811fc'), 'psr/log-implementation' => array('provided' => array(0 => '1.0')), 'react/cache' => array('pretty_version' => 'v1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => '44a568925556b0bd8cacc7b49fb0f1cf0d706a0c'), 'react/child-process' => array('pretty_version' => 'v0.6.1', 'version' => '0.6.1.0', 'aliases' => array(), 'reference' => '6895afa583d51dc10a4b9e93cd3bce17b3b77ac3'), 'react/dns' => array('pretty_version' => 'v1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => '665260757171e2ab17485b44e7ffffa7acb6ca1f'), 'react/event-loop' => array('pretty_version' => 'v1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '6d24de090cd59cfc830263cfba965be77b563c13'), 'react/http' => array('pretty_version' => 'v1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => '754b0c18545d258922ffa907f3b18598280fdecd'), 'react/promise' => array('pretty_version' => 'v2.8.0', 'version' => '2.8.0.0', 'aliases' => array(), 'reference' => 'f3cff96a19736714524ca0dd1d4130de73dbbbc4'), 'react/promise-stream' => array('pretty_version' => 'v1.2.0', 'version' => '1.2.0.0', 'aliases' => array(), 'reference' => '6384d8b76cf7dcc44b0bf3343fb2b2928412d1fe'), 'react/promise-timer' => array('pretty_version' => 'v1.6.0', 'version' => '1.6.0.0', 'aliases' => array(), 'reference' => 'daee9baf6ef30c43ea4c86399f828bb5f558f6e6'), 'react/socket' => array('pretty_version' => 'v1.6.0', 'version' => '1.6.0.0', 'aliases' => array(), 'reference' => 'e2b96b23a13ca9b41ab343268dbce3f8ef4d524a'), 'react/stream' => array('pretty_version' => 'v1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '7c02b510ee3f582c810aeccd3a197b9c2f52ff1a'), 'ringcentral/psr7' => array('pretty_version' => '1.3.0', 'version' => '1.3.0.0', 'aliases' => array(), 'reference' => '360faaec4b563958b673fb52bbe94e37f14bc686'), 'roave/signature' => array('pretty_version' => '1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => 'c4e8a59946bad694ab5682a76e7884a9157a8a2c'), 'sebastian/cli-parser' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '442e7c7e687e42adc03470c7b668bc4b2402c0b2'), 'sebastian/code-unit' => array('pretty_version' => '1.0.8', 'version' => '1.0.8.0', 'aliases' => array(), 'reference' => '1fc9f64c0927627ef78ba436c9b17d967e68e120'), 'sebastian/code-unit-reverse-lookup' => array('pretty_version' => '2.0.3', 'version' => '2.0.3.0', 'aliases' => array(), 'reference' => 'ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5'), 'sebastian/comparator' => array('pretty_version' => '4.0.6', 'version' => '4.0.6.0', 'aliases' => array(), 'reference' => '55f4261989e546dc112258c7a75935a81a7ce382'), 'sebastian/complexity' => array('pretty_version' => '2.0.2', 'version' => '2.0.2.0', 'aliases' => array(), 'reference' => '739b35e53379900cc9ac327b2147867b8b6efd88'), 'sebastian/diff' => array('pretty_version' => '4.0.4', 'version' => '4.0.4.0', 'aliases' => array(), 'reference' => '3461e3fccc7cfdfc2720be910d3bd73c69be590d'), 'sebastian/environment' => array('pretty_version' => '5.1.3', 'version' => '5.1.3.0', 'aliases' => array(), 'reference' => '388b6ced16caa751030f6a69e588299fa09200ac'), 'sebastian/exporter' => array('pretty_version' => '4.0.3', 'version' => '4.0.3.0', 'aliases' => array(), 'reference' => 'd89cc98761b8cb5a1a235a6b703ae50d34080e65'), 'sebastian/global-state' => array('pretty_version' => '5.0.2', 'version' => '5.0.2.0', 'aliases' => array(), 'reference' => 'a90ccbddffa067b51f574dea6eb25d5680839455'), 'sebastian/lines-of-code' => array('pretty_version' => '1.0.3', 'version' => '1.0.3.0', 'aliases' => array(), 'reference' => 'c1c2e997aa3146983ed888ad08b15470a2e22ecc'), 'sebastian/object-enumerator' => array('pretty_version' => '4.0.4', 'version' => '4.0.4.0', 'aliases' => array(), 'reference' => '5c9eeac41b290a3712d88851518825ad78f45c71'), 'sebastian/object-reflector' => array('pretty_version' => '2.0.4', 'version' => '2.0.4.0', 'aliases' => array(), 'reference' => 'b4f479ebdbf63ac605d183ece17d8d7fe49c15c7'), 'sebastian/recursion-context' => array('pretty_version' => '4.0.4', 'version' => '4.0.4.0', 'aliases' => array(), 'reference' => 'cd9d8cf3c5804de4341c283ed787f099f5506172'), 'sebastian/resource-operations' => array('pretty_version' => '3.0.3', 'version' => '3.0.3.0', 'aliases' => array(), 'reference' => '0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8'), 'sebastian/type' => array('pretty_version' => '2.3.1', 'version' => '2.3.1.0', 'aliases' => array(), 'reference' => '81cd61ab7bbf2de744aba0ea61fae32f721df3d2'), 'sebastian/version' => array('pretty_version' => '3.0.2', 'version' => '3.0.2.0', 'aliases' => array(), 'reference' => 'c6c1022351a901512170118436c764e473f6de8c'), 'seld/jsonlint' => array('pretty_version' => '1.8.3', 'version' => '1.8.3.0', 'aliases' => array(), 'reference' => '9ad6ce79c342fbd44df10ea95511a1b24dee5b57'), 'symfony/console' => array('pretty_version' => 'v4.4.21', 'version' => '4.4.21.0', 'aliases' => array(), 'reference' => '1ba4560dbbb9fcf5ae28b61f71f49c678086cf23'), 'symfony/finder' => array('pretty_version' => 'v4.4.16', 'version' => '4.4.16.0', 'aliases' => array(), 'reference' => '26f63b8d4e92f2eecd90f6791a563ebb001abe31'), 'symfony/polyfill-ctype' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'c6c942b1ac76c82448322025e084cadc56048b4e'), 'symfony/polyfill-mbstring' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => '5232de97ee3b75b0360528dae24e73db49566ab1'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'a678b42e92f86eca04b7fa4c0f6f19d097fb69e2'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'dc3063ba22c2a1fd2f45ed856374d79114998f91'), 'symfony/process' => array('pretty_version' => 'v5.2.4', 'version' => '5.2.4.0', 'aliases' => array(), 'reference' => '313a38f09c77fbcdc1d223e57d368cea76a2fd2f'), 'symfony/service-contracts' => array('pretty_version' => 'v1.1.8', 'version' => '1.1.8.0', 'aliases' => array(), 'reference' => 'ffc7f5692092df31515df2a5ecf3b7302b3ddacf'), 'theseer/tokenizer' => array('pretty_version' => '1.2.0', 'version' => '1.2.0.0', 'aliases' => array(), 'reference' => '75a63c33a8577608444246075ea0af0d052e452a'), 'vaimo/composer-patches' => array('pretty_version' => '4.22.4', 'version' => '4.22.4.0', 'aliases' => array(), 'reference' => '3da4cdf03fb4dc8d92b3d435de183f6044d679d6'), 'vaimo/topological-sort' => array('pretty_version' => '1.0.0', 'version' => '1.0.0.0', 'aliases' => array(), 'reference' => 'e19b93df2bac0e995ecd4b982ec4ea2fb1131e64'), 'webmozart/assert' => array('pretty_version' => '1.10.0', 'version' => '1.10.0.0', 'aliases' => array(), 'reference' => '6964c76c7804814a842473e0c8fd15bab0f18e25')));
    private static $canGetVendors;
    private static $installedByVendor = array();
    public static function getInstalledPackages()
    {
        $packages = array();
        foreach (self::getInstalled() as $installed) {
            $packages[] = \array_keys($installed['versions']);
        }
        if (1 === \count($packages)) {
            return $packages[0];
        }
        return \array_keys(\array_flip(\call_user_func_array('array_merge', $packages)));
    }
    public static function isInstalled($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (isset($installed['versions'][$packageName])) {
                return \true;
            }
        }
        return \false;
    }
    public static function satisfies(\RectorPrefix20210518\Composer\Semver\VersionParser $parser, $packageName, $constraint)
    {
        $constraint = $parser->parseConstraints($constraint);
        $provided = $parser->parseConstraints(self::getVersionRanges($packageName));
        return $provided->matches($constraint);
    }
    public static function getVersionRanges($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            $ranges = array();
            if (isset($installed['versions'][$packageName]['pretty_version'])) {
                $ranges[] = $installed['versions'][$packageName]['pretty_version'];
            }
            if (\array_key_exists('aliases', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['aliases']);
            }
            if (\array_key_exists('replaced', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['replaced']);
            }
            if (\array_key_exists('provided', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['provided']);
            }
            return \implode(' || ', $ranges);
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getPrettyVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['pretty_version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['pretty_version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getReference($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['reference'])) {
                return null;
            }
            return $installed['versions'][$packageName]['reference'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getRootPackage()
    {
        $installed = self::getInstalled();
        return $installed[0]['root'];
    }
    public static function getRawData()
    {
        return self::$installed;
    }
    public static function reload($data)
    {
        self::$installed = $data;
        self::$installedByVendor = array();
    }
    private static function getInstalled()
    {
        if (null === self::$canGetVendors) {
            self::$canGetVendors = \method_exists('RectorPrefix20210518\\Composer\\Autoload\\ClassLoader', 'getRegisteredLoaders');
        }
        $installed = array();
        if (self::$canGetVendors) {
            foreach (\RectorPrefix20210518\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
                if (isset(self::$installedByVendor[$vendorDir])) {
                    $installed[] = self::$installedByVendor[$vendorDir];
                } elseif (\is_file($vendorDir . '/composer/installed.php')) {
                    $installed[] = self::$installedByVendor[$vendorDir] = (require $vendorDir . '/composer/installed.php');
                }
            }
        }
        $installed[] = self::$installed;
        return $installed;
    }
}
