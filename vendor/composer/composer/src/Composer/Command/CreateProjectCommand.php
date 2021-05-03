<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210503\Composer\Command;

use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\Factory;
use RectorPrefix20210503\Composer\Installer;
use RectorPrefix20210503\Composer\Installer\ProjectInstaller;
use RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Package\BasePackage;
use RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation;
use RectorPrefix20210503\Composer\Package\Version\VersionSelector;
use RectorPrefix20210503\Composer\Package\AliasPackage;
use RectorPrefix20210503\Composer\Repository\RepositoryFactory;
use RectorPrefix20210503\Composer\Repository\CompositeRepository;
use RectorPrefix20210503\Composer\Repository\PlatformRepository;
use RectorPrefix20210503\Composer\Repository\InstalledFilesystemRepository;
use RectorPrefix20210503\Composer\Repository\RepositorySet;
use RectorPrefix20210503\Composer\Script\ScriptEvents;
use RectorPrefix20210503\Composer\Util\Silencer;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Symfony\Component\Finder\Finder;
use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\Config\JsonConfigSource;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
/**
 * Install a package as new project into new directory.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Tobias Munk <schmunk@usrbin.de>
 * @author Nils Adermann <naderman@naderman.de>
 */
class CreateProjectCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    /**
     * @var SuggestedPackagesReporter
     */
    protected $suggestedPackagesReporter;
    protected function configure()
    {
        $this->setName('create-project')->setDescription('Creates new project from a package into given directory.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('package', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Package name to be installed'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('directory', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Directory where the files should be created'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('version', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Version, will default to latest'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('stability', 's', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Minimum-stability allowed (unless a version is specified).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-source', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Forces installation from package sources when possible, including VCS information.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-dist', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Forces installation from package dist even for dev versions.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('repository', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED | \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY, 'Add custom repositories to look the package up, either by URL or using JSON arrays'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('repository-url', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'DEPRECATED: Use --repository instead.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('add-repository', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Add the custom repository in the composer.json. If a lock file is present it will be deleted and an update will be run instead of install.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Enables installation of require-dev packages (enabled by default, only present for BC).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables installation of require-dev packages.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-custom-installers', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'DEPRECATED: Use no-plugins instead.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-scripts', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Whether to prevent execution of all defined scripts in the root package.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-progress', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Do not output download progress.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-secure-http', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disable the secure-http config option temporarily while installing the root package. Use at your own risk. Using this flag is a bad idea.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('keep-vcs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Whether to prevent deleting the vcs folder.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('remove-vcs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Whether to force deletion of the vcs folder without prompting.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-install', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Whether to skip installation of the package dependencies.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-req', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED | \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY, 'Ignore a specific platform requirement (php & ext- packages).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-reqs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Ignore all platform requirements (php & ext- packages).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ask', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Whether to ask for project directory.')))->setHelp(<<<EOT
The <info>create-project</info> command creates a new project from a given
package into a new directory. If executed without params and in a directory
with a composer.json file it installs the packages for the current project.

You can use this command to bootstrap new projects or setup a clean
version-controlled installation for developers of your project.

<info>php composer.phar create-project vendor/project target-directory [version]</info>

You can also specify the version with the package name using = or : as separator.

<info>php composer.phar create-project vendor/project:version target-directory</info>

To install unstable packages, either specify the version you want, or use the
--stability=dev (where dev can be one of RC, beta, alpha or dev).

To setup a developer workable version you should create the project using the source
controlled code by appending the <info>'--prefer-source'</info> flag.

To install a package from another repository than the default one you
can pass the <info>'--repository=https://myrepository.org'</info> flag.

Read more at https://getcomposer.org/doc/03-cli.md#create-project
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $config = \RectorPrefix20210503\Composer\Factory::createConfig();
        $io = $this->getIO();
        list($preferSource, $preferDist) = $this->getPreferredInstallOptions($config, $input, \true);
        if ($input->getOption('dev')) {
            $io->writeError('<warning>You are using the deprecated option "dev". Dev packages are installed by default now.</warning>');
        }
        if ($input->getOption('no-custom-installers')) {
            $io->writeError('<warning>You are using the deprecated option "no-custom-installers". Use "no-plugins" instead.</warning>');
            $input->setOption('no-plugins', \true);
        }
        if ($input->isInteractive() && $input->getOption('ask')) {
            $parts = \explode("/", \strtolower($input->getArgument('package')), 2);
            $input->setArgument('directory', $io->ask('New project directory [<comment>' . \array_pop($parts) . '</comment>]: '));
        }
        $ignorePlatformReqs = $input->getOption('ignore-platform-reqs') ?: ($input->getOption('ignore-platform-req') ?: \false);
        return $this->installProject($io, $config, $input, $input->getArgument('package'), $input->getArgument('directory'), $input->getArgument('version'), $input->getOption('stability'), $preferSource, $preferDist, !$input->getOption('no-dev'), $input->getOption('repository') ?: $input->getOption('repository-url'), $input->getOption('no-plugins'), $input->getOption('no-scripts'), $input->getOption('no-progress'), $input->getOption('no-install'), $ignorePlatformReqs, !$input->getOption('no-secure-http'), $input->getOption('add-repository'));
    }
    public function installProject(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, $packageName, $directory = null, $packageVersion = null, $stability = 'stable', $preferSource = \false, $preferDist = \false, $installDevPackages = \false, $repositories = null, $disablePlugins = \false, $noScripts = \false, $noProgress = \false, $noInstall = \false, $ignorePlatformReqs = \false, $secureHttp = \true, $addRepository = \false)
    {
        $oldCwd = \getcwd();
        if ($repositories !== null && !\is_array($repositories)) {
            $repositories = (array) $repositories;
        }
        // we need to manually load the configuration to pass the auth credentials to the io interface!
        $io->loadConfiguration($config);
        $this->suggestedPackagesReporter = new \RectorPrefix20210503\Composer\Installer\SuggestedPackagesReporter($io);
        if ($packageName !== null) {
            $installedFromVcs = $this->installRootPackage($io, $config, $packageName, $directory, $packageVersion, $stability, $preferSource, $preferDist, $installDevPackages, $repositories, $disablePlugins, $noScripts, $noProgress, $ignorePlatformReqs, $secureHttp);
        } else {
            $installedFromVcs = \false;
        }
        if ($repositories !== null && $addRepository && \is_file('composer.lock')) {
            \unlink('composer.lock');
        }
        $composer = \RectorPrefix20210503\Composer\Factory::create($io, null, $disablePlugins);
        // add the repository to the composer.json and use it for the install run later
        if ($repositories !== null && $addRepository) {
            foreach ($repositories as $index => $repo) {
                $repoConfig = \RectorPrefix20210503\Composer\Repository\RepositoryFactory::configFromString($io, $composer->getConfig(), $repo, \true);
                $composerJsonRepositoriesConfig = $composer->getConfig()->getRepositories();
                $name = \RectorPrefix20210503\Composer\Repository\RepositoryFactory::generateRepositoryName($index, $repoConfig, $composerJsonRepositoriesConfig);
                $configSource = new \RectorPrefix20210503\Composer\Config\JsonConfigSource(new \RectorPrefix20210503\Composer\Json\JsonFile('composer.json'));
                if (isset($repoConfig['packagist']) && $repoConfig === array('packagist' => \false) || isset($repoConfig['packagist.org']) && $repoConfig === array('packagist.org' => \false)) {
                    $configSource->addRepository('packagist.org', \false);
                } else {
                    $configSource->addRepository($name, $repoConfig, \false);
                }
                $composer = \RectorPrefix20210503\Composer\Factory::create($io, null, $disablePlugins);
            }
        }
        $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem($process);
        if ($noScripts === \false) {
            // dispatch event
            $composer->getEventDispatcher()->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::POST_ROOT_PACKAGE_INSTALL, $installDevPackages);
        }
        // use the new config including the newly installed project
        $config = $composer->getConfig();
        list($preferSource, $preferDist) = $this->getPreferredInstallOptions($config, $input);
        // install dependencies of the created project
        if ($noInstall === \false) {
            $composer->getInstallationManager()->setOutputProgress(!$noProgress);
            $installer = \RectorPrefix20210503\Composer\Installer::create($io, $composer);
            $installer->setPreferSource($preferSource)->setPreferDist($preferDist)->setDevMode($installDevPackages)->setRunScripts(!$noScripts)->setIgnorePlatformRequirements($ignorePlatformReqs)->setSuggestedPackagesReporter($this->suggestedPackagesReporter)->setOptimizeAutoloader($config->get('optimize-autoloader'))->setClassMapAuthoritative($config->get('classmap-authoritative'))->setApcuAutoloader($config->get('apcu-autoloader'));
            if (!$composer->getLocker()->isLocked()) {
                $installer->setUpdate(\true);
            }
            if ($disablePlugins) {
                $installer->disablePlugins();
            }
            $status = $installer->run();
            if (0 !== $status) {
                return $status;
            }
        }
        $hasVcs = $installedFromVcs;
        if (!$input->getOption('keep-vcs') && $installedFromVcs && ($input->getOption('remove-vcs') || !$io->isInteractive() || $io->askConfirmation('<info>Do you want to remove the existing VCS (.git, .svn..) history?</info> [<comment>Y,n</comment>]? '))) {
            $finder = new \RectorPrefix20210503\Symfony\Component\Finder\Finder();
            $finder->depth(0)->directories()->in(\getcwd())->ignoreVCS(\false)->ignoreDotFiles(\false);
            foreach (array('.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg', '.fslckout', '_FOSSIL_') as $vcsName) {
                $finder->name($vcsName);
            }
            try {
                $dirs = \iterator_to_array($finder);
                unset($finder);
                foreach ($dirs as $dir) {
                    if (!$fs->removeDirectory($dir)) {
                        throw new \RuntimeException('Could not remove ' . $dir);
                    }
                }
            } catch (\Exception $e) {
                $io->writeError('<error>An error occurred while removing the VCS metadata: ' . $e->getMessage() . '</error>');
            }
            $hasVcs = \false;
        }
        // rewriting self.version dependencies with explicit version numbers if the package's vcs metadata is gone
        if (!$hasVcs) {
            $package = $composer->getPackage();
            $configSource = new \RectorPrefix20210503\Composer\Config\JsonConfigSource(new \RectorPrefix20210503\Composer\Json\JsonFile('composer.json'));
            foreach (\RectorPrefix20210503\Composer\Package\BasePackage::$supportedLinkTypes as $type => $meta) {
                foreach ($package->{'get' . $meta['method']}() as $link) {
                    if ($link->getPrettyConstraint() === 'self.version') {
                        $configSource->addLink($type, $link->getTarget(), $package->getPrettyVersion());
                    }
                }
            }
        }
        if ($noScripts === \false) {
            // dispatch event
            $composer->getEventDispatcher()->dispatchScript(\RectorPrefix20210503\Composer\Script\ScriptEvents::POST_CREATE_PROJECT_CMD, $installDevPackages);
        }
        \chdir($oldCwd);
        $vendorComposerDir = $config->get('vendor-dir') . '/composer';
        if (\is_dir($vendorComposerDir) && $fs->isDirEmpty($vendorComposerDir)) {
            \RectorPrefix20210503\Composer\Util\Silencer::call('rmdir', $vendorComposerDir);
            $vendorDir = $config->get('vendor-dir');
            if (\is_dir($vendorDir) && $fs->isDirEmpty($vendorDir)) {
                \RectorPrefix20210503\Composer\Util\Silencer::call('rmdir', $vendorDir);
            }
        }
        return 0;
    }
    protected function installRootPackage(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $packageName, $directory = null, $packageVersion = null, $stability = 'stable', $preferSource = \false, $preferDist = \false, $installDevPackages = \false, array $repositories = null, $disablePlugins = \false, $noScripts = \false, $noProgress = \false, $ignorePlatformReqs = \false, $secureHttp = \true)
    {
        if (!$secureHttp) {
            $config->merge(array('config' => array('secure-http' => \false)));
        }
        $parser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
        $requirements = $parser->parseNameVersionPairs(array($packageName));
        $name = \strtolower($requirements[0]['name']);
        if (!$packageVersion && isset($requirements[0]['version'])) {
            $packageVersion = $requirements[0]['version'];
        }
        // if no directory was specified, use the 2nd part of the package name
        if (null === $directory) {
            $parts = \explode("/", $name, 2);
            $directory = \getcwd() . \DIRECTORY_SEPARATOR . \array_pop($parts);
        }
        $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem($process);
        if (!$fs->isAbsolutePath($directory)) {
            $directory = \getcwd() . \DIRECTORY_SEPARATOR . $directory;
        }
        $io->writeError('<info>Creating a "' . $packageName . '" project at "' . $fs->findShortestPath(\getcwd(), $directory, \true) . '"</info>');
        if (\file_exists($directory)) {
            if (!\is_dir($directory)) {
                throw new \InvalidArgumentException('Cannot create project directory at "' . $directory . '", it exists as a file.');
            }
            if (!$fs->isDirEmpty($directory)) {
                throw new \InvalidArgumentException('Project directory "' . $directory . '" is not empty.');
            }
        }
        if (null === $stability) {
            if (null === $packageVersion) {
                $stability = 'stable';
            } elseif (\preg_match('{^[^,\\s]*?@(' . \implode('|', \array_keys(\RectorPrefix20210503\Composer\Package\BasePackage::$stabilities)) . ')$}i', $packageVersion, $match)) {
                $stability = $match[1];
            } else {
                $stability = \RectorPrefix20210503\Composer\Package\Version\VersionParser::parseStability($packageVersion);
            }
        }
        $stability = \RectorPrefix20210503\Composer\Package\Version\VersionParser::normalizeStability($stability);
        if (!isset(\RectorPrefix20210503\Composer\Package\BasePackage::$stabilities[$stability])) {
            throw new \InvalidArgumentException('Invalid stability provided (' . $stability . '), must be one of: ' . \implode(', ', \array_keys(\RectorPrefix20210503\Composer\Package\BasePackage::$stabilities)));
        }
        $composer = \RectorPrefix20210503\Composer\Factory::create($io, $config->all(), $disablePlugins);
        $config = $composer->getConfig();
        $rm = $composer->getRepositoryManager();
        $repositorySet = new \RectorPrefix20210503\Composer\Repository\RepositorySet($stability);
        if (null === $repositories) {
            $repositorySet->addRepository(new \RectorPrefix20210503\Composer\Repository\CompositeRepository(\RectorPrefix20210503\Composer\Repository\RepositoryFactory::defaultRepos($io, $config, $rm)));
        } else {
            foreach ($repositories as $repo) {
                $repoConfig = \RectorPrefix20210503\Composer\Repository\RepositoryFactory::configFromString($io, $config, $repo, \true);
                if (isset($repoConfig['packagist']) && $repoConfig === array('packagist' => \false) || isset($repoConfig['packagist.org']) && $repoConfig === array('packagist.org' => \false)) {
                    continue;
                }
                $repositorySet->addRepository(\RectorPrefix20210503\Composer\Repository\RepositoryFactory::createRepo($io, $config, $repoConfig, $rm));
            }
        }
        $platformOverrides = $config->get('platform') ?: array();
        $platformRepo = new \RectorPrefix20210503\Composer\Repository\PlatformRepository(array(), $platformOverrides);
        // find the latest version if there are multiple
        $versionSelector = new \RectorPrefix20210503\Composer\Package\Version\VersionSelector($repositorySet, $platformRepo);
        $package = $versionSelector->findBestCandidate($name, $packageVersion, $stability, $ignorePlatformReqs);
        if (!$package) {
            $errorMessage = "Could not find package {$name} with " . ($packageVersion ? "version {$packageVersion}" : "stability {$stability}");
            if (\true !== $ignorePlatformReqs && $versionSelector->findBestCandidate($name, $packageVersion, $stability, \true)) {
                throw new \InvalidArgumentException($errorMessage . ' in a version installable using your PHP version, PHP extensions and Composer version.');
            }
            throw new \InvalidArgumentException($errorMessage . '.');
        }
        // handler Ctrl+C for unix-like systems
        if (\function_exists('pcntl_async_signals') && \function_exists('pcntl_signal')) {
            @\mkdir($directory, 0777, \true);
            if ($realDir = \realpath($directory)) {
                \pcntl_async_signals(\true);
                \pcntl_signal(\SIGINT, function () use($realDir) {
                    $fs = new \RectorPrefix20210503\Composer\Util\Filesystem();
                    $fs->removeDirectory($realDir);
                    exit(130);
                });
            }
        }
        // handler Ctrl+C for Windows on PHP 7.4+
        if (\function_exists('sapi_windows_set_ctrl_handler') && \PHP_SAPI === 'cli') {
            @\mkdir($directory, 0777, \true);
            if ($realDir = \realpath($directory)) {
                \sapi_windows_set_ctrl_handler(function () use($realDir) {
                    $fs = new \RectorPrefix20210503\Composer\Util\Filesystem();
                    $fs->removeDirectory($realDir);
                    exit(130);
                });
            }
        }
        // avoid displaying 9999999-dev as version if default-branch was selected
        if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage && $package->getPrettyVersion() === \RectorPrefix20210503\Composer\Package\Version\VersionParser::DEFAULT_BRANCH_ALIAS) {
            $package = $package->getAliasOf();
        }
        $io->writeError('<info>Installing ' . $package->getName() . ' (' . $package->getFullPrettyVersion(\false) . ')</info>');
        if ($disablePlugins) {
            $io->writeError('<info>Plugins have been disabled.</info>');
        }
        if ($package instanceof \RectorPrefix20210503\Composer\Package\AliasPackage) {
            $package = $package->getAliasOf();
        }
        $dm = $composer->getDownloadManager();
        $dm->setPreferSource($preferSource)->setPreferDist($preferDist);
        $projectInstaller = new \RectorPrefix20210503\Composer\Installer\ProjectInstaller($directory, $dm, $fs);
        $im = $composer->getInstallationManager();
        $im->setOutputProgress(!$noProgress);
        $im->addInstaller($projectInstaller);
        $im->execute(new \RectorPrefix20210503\Composer\Repository\InstalledFilesystemRepository(new \RectorPrefix20210503\Composer\Json\JsonFile('php://memory')), array(new \RectorPrefix20210503\Composer\DependencyResolver\Operation\InstallOperation($package)));
        $im->notifyInstalls($io);
        // collect suggestions
        $this->suggestedPackagesReporter->addSuggestionsFromPackage($package);
        $installedFromVcs = 'source' === $package->getInstallationSource();
        $io->writeError('<info>Created project in ' . $directory . '</info>');
        \chdir($directory);
        $_SERVER['COMPOSER_ROOT_VERSION'] = $package->getPrettyVersion();
        \putenv('COMPOSER_ROOT_VERSION=' . $_SERVER['COMPOSER_ROOT_VERSION']);
        return $installedFromVcs;
    }
}
