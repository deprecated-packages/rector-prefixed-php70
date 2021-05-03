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
namespace RectorPrefix20210503\Composer;

use RectorPrefix20210503\Composer\Config\JsonConfigSource;
use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Package\Archiver;
use RectorPrefix20210503\Composer\Package\Version\VersionGuesser;
use RectorPrefix20210503\Composer\Package\RootPackageInterface;
use RectorPrefix20210503\Composer\Repository\RepositoryManager;
use RectorPrefix20210503\Composer\Repository\RepositoryFactory;
use RectorPrefix20210503\Composer\Repository\WritableRepositoryInterface;
use RectorPrefix20210503\Composer\Util\Filesystem;
use RectorPrefix20210503\Composer\Util\Platform;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Util\HttpDownloader;
use RectorPrefix20210503\Composer\Util\Loop;
use RectorPrefix20210503\Composer\Util\Silencer;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\EventDispatcher\Event;
use RectorPrefix20210503\Seld\JsonLint\DuplicateKeyException;
use RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatter;
use RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterStyle;
use RectorPrefix20210503\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher;
use RectorPrefix20210503\Composer\Autoload\AutoloadGenerator;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Downloader\TransportException;
use RectorPrefix20210503\Seld\JsonLint\JsonParser;
/**
 * Creates a configured instance of composer.
 *
 * @author Ryan Weaver <ryan@knplabs.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Nils Adermann <naderman@naderman.de>
 */
class Factory
{
    /**
     * @throws \RuntimeException
     * @return string
     */
    protected static function getHomeDir()
    {
        $home = \getenv('COMPOSER_HOME');
        if ($home) {
            return $home;
        }
        if (\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            if (!\getenv('APPDATA')) {
                throw new \RuntimeException('The APPDATA or COMPOSER_HOME environment variable must be set for composer to run correctly');
            }
            return \rtrim(\strtr(\getenv('APPDATA'), '\\', '/'), '/') . '/Composer';
        }
        $userDir = self::getUserDir();
        $dirs = array();
        if (self::useXdg()) {
            // XDG Base Directory Specifications
            $xdgConfig = \getenv('XDG_CONFIG_HOME');
            if (!$xdgConfig) {
                $xdgConfig = $userDir . '/.config';
            }
            $dirs[] = $xdgConfig . '/composer';
        }
        $dirs[] = $userDir . '/.composer';
        // select first dir which exists of: $XDG_CONFIG_HOME/composer or ~/.composer
        foreach ($dirs as $dir) {
            if (\RectorPrefix20210503\Composer\Util\Silencer::call('is_dir', $dir)) {
                return $dir;
            }
        }
        // if none exists, we default to first defined one (XDG one if system uses it, or ~/.composer otherwise)
        return $dirs[0];
    }
    /**
     * @param  string $home
     * @return string
     */
    protected static function getCacheDir($home)
    {
        $cacheDir = \getenv('COMPOSER_CACHE_DIR');
        if ($cacheDir) {
            return $cacheDir;
        }
        $homeEnv = \getenv('COMPOSER_HOME');
        if ($homeEnv) {
            return $homeEnv . '/cache';
        }
        if (\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            if ($cacheDir = \getenv('LOCALAPPDATA')) {
                $cacheDir .= '/Composer';
            } else {
                $cacheDir = $home . '/cache';
            }
            return \rtrim(\strtr($cacheDir, '\\', '/'), '/');
        }
        $userDir = self::getUserDir();
        if ($home === $userDir . '/.composer' && \is_dir($home . '/cache')) {
            return $home . '/cache';
        }
        if (self::useXdg()) {
            $xdgCache = \getenv('XDG_CACHE_HOME') ?: $userDir . '/.cache';
            return $xdgCache . '/composer';
        }
        return $home . '/cache';
    }
    /**
     * @param  string $home
     * @return string
     */
    protected static function getDataDir($home)
    {
        $homeEnv = \getenv('COMPOSER_HOME');
        if ($homeEnv) {
            return $homeEnv;
        }
        if (\RectorPrefix20210503\Composer\Util\Platform::isWindows()) {
            return \strtr($home, '\\', '/');
        }
        $userDir = self::getUserDir();
        if ($home !== $userDir . '/.composer' && self::useXdg()) {
            $xdgData = \getenv('XDG_DATA_HOME') ?: $userDir . '/.local/share';
            return $xdgData . '/composer';
        }
        return $home;
    }
    /**
     * @param  IOInterface|null $io
     * @return Config
     */
    public static function createConfig(\RectorPrefix20210503\Composer\IO\IOInterface $io = null, $cwd = null)
    {
        $cwd = $cwd ?: \getcwd();
        $config = new \RectorPrefix20210503\Composer\Config(\true, $cwd);
        // determine and add main dirs to the config
        $home = self::getHomeDir();
        $config->merge(array('config' => array('home' => $home, 'cache-dir' => self::getCacheDir($home), 'data-dir' => self::getDataDir($home))));
        // load global config
        $file = new \RectorPrefix20210503\Composer\Json\JsonFile($config->get('home') . '/config.json');
        if ($file->exists()) {
            if ($io && $io->isDebug()) {
                $io->writeError('Loading config file ' . $file->getPath());
            }
            $config->merge($file->read());
        }
        $config->setConfigSource(new \RectorPrefix20210503\Composer\Config\JsonConfigSource($file));
        $htaccessProtect = (bool) $config->get('htaccess-protect');
        if ($htaccessProtect) {
            // Protect directory against web access. Since HOME could be
            // the www-data's user home and be web-accessible it is a
            // potential security risk
            $dirs = array($config->get('home'), $config->get('cache-dir'), $config->get('data-dir'));
            foreach ($dirs as $dir) {
                if (!\file_exists($dir . '/.htaccess')) {
                    if (!\is_dir($dir)) {
                        \RectorPrefix20210503\Composer\Util\Silencer::call('mkdir', $dir, 0777, \true);
                    }
                    \RectorPrefix20210503\Composer\Util\Silencer::call('file_put_contents', $dir . '/.htaccess', 'Deny from all');
                }
            }
        }
        // load global auth file
        $file = new \RectorPrefix20210503\Composer\Json\JsonFile($config->get('home') . '/auth.json');
        if ($file->exists()) {
            if ($io && $io->isDebug()) {
                $io->writeError('Loading config file ' . $file->getPath());
            }
            $config->merge(array('config' => $file->read()));
        }
        $config->setAuthConfigSource(new \RectorPrefix20210503\Composer\Config\JsonConfigSource($file, \true));
        // load COMPOSER_AUTH environment variable if set
        if ($composerAuthEnv = \getenv('COMPOSER_AUTH')) {
            $authData = \json_decode($composerAuthEnv, \true);
            if (null === $authData) {
                throw new \UnexpectedValueException('COMPOSER_AUTH environment variable is malformed, should be a valid JSON object');
            }
            if ($io && $io->isDebug()) {
                $io->writeError('Loading auth config from COMPOSER_AUTH');
            }
            $config->merge(array('config' => $authData));
        }
        return $config;
    }
    public static function getComposerFile()
    {
        return \trim(\getenv('COMPOSER')) ?: './composer.json';
    }
    public static function getLockFile($composerFile)
    {
        return "json" === \pathinfo($composerFile, \PATHINFO_EXTENSION) ? \substr($composerFile, 0, -4) . 'lock' : $composerFile . '.lock';
    }
    public static function createAdditionalStyles()
    {
        return array('highlight' => new \RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterStyle('red'), 'warning' => new \RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow'));
    }
    /**
     * Creates a ConsoleOutput instance
     *
     * @return ConsoleOutput
     */
    public static function createOutput()
    {
        $styles = self::createAdditionalStyles();
        $formatter = new \RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatter(\false, $styles);
        return new \RectorPrefix20210503\Symfony\Component\Console\Output\ConsoleOutput(\RectorPrefix20210503\Symfony\Component\Console\Output\ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
    }
    /**
     * Creates a Composer instance
     *
     * @param  IOInterface               $io             IO instance
     * @param  array|string|null         $localConfig    either a configuration array or a filename to read from, if null it will
     *                                                   read from the default filename
     * @param  bool                      $disablePlugins Whether plugins should not be loaded
     * @param  bool                      $fullLoad       Whether to initialize everything or only main project stuff (used when loading the global composer)
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return Composer
     */
    public function createComposer(\RectorPrefix20210503\Composer\IO\IOInterface $io, $localConfig = null, $disablePlugins = \false, $cwd = null, $fullLoad = \true)
    {
        $cwd = $cwd ?: \getcwd();
        // load Composer configuration
        if (null === $localConfig) {
            $localConfig = static::getComposerFile();
        }
        if (\is_string($localConfig)) {
            $composerFile = $localConfig;
            $file = new \RectorPrefix20210503\Composer\Json\JsonFile($localConfig, null, $io);
            if (!$file->exists()) {
                if ($localConfig === './composer.json' || $localConfig === 'composer.json') {
                    $message = 'Composer could not find a composer.json file in ' . $cwd;
                } else {
                    $message = 'Composer could not find the config file: ' . $localConfig;
                }
                $instructions = $fullLoad ? 'To initialize a project, please create a composer.json file as described in the https://getcomposer.org/ "Getting Started" section' : '';
                throw new \InvalidArgumentException($message . \PHP_EOL . $instructions);
            }
            $file->validateSchema(\RectorPrefix20210503\Composer\Json\JsonFile::LAX_SCHEMA);
            $jsonParser = new \RectorPrefix20210503\Seld\JsonLint\JsonParser();
            try {
                $jsonParser->parse(\file_get_contents($localConfig), \RectorPrefix20210503\Seld\JsonLint\JsonParser::DETECT_KEY_CONFLICTS);
            } catch (\RectorPrefix20210503\Seld\JsonLint\DuplicateKeyException $e) {
                $details = $e->getDetails();
                $io->writeError('<warning>Key ' . $details['key'] . ' is a duplicate in ' . $localConfig . ' at line ' . $details['line'] . '</warning>');
            }
            $localConfig = $file->read();
        }
        // Load config and override with local config/auth config
        $config = static::createConfig($io, $cwd);
        $config->merge($localConfig);
        if (isset($composerFile)) {
            $io->writeError('Loading config file ' . $composerFile . ' (' . \realpath($composerFile) . ')', \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
            $config->setConfigSource(new \RectorPrefix20210503\Composer\Config\JsonConfigSource(new \RectorPrefix20210503\Composer\Json\JsonFile(\realpath($composerFile), null, $io)));
            $localAuthFile = new \RectorPrefix20210503\Composer\Json\JsonFile(\dirname(\realpath($composerFile)) . '/auth.json', null, $io);
            if ($localAuthFile->exists()) {
                $io->writeError('Loading config file ' . $localAuthFile->getPath(), \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
                $config->merge(array('config' => $localAuthFile->read()));
                $config->setAuthConfigSource(new \RectorPrefix20210503\Composer\Config\JsonConfigSource($localAuthFile, \true));
            }
        }
        $vendorDir = $config->get('vendor-dir');
        // initialize composer
        $composer = new \RectorPrefix20210503\Composer\Composer();
        $composer->setConfig($config);
        if ($fullLoad) {
            // load auth configs into the IO instance
            $io->loadConfiguration($config);
            // load existing Composer\InstalledVersions instance if available
            if (!\class_exists('RectorPrefix20210503\\Composer\\InstalledVersions', \false) && \file_exists($installedVersionsPath = $config->get('vendor-dir') . '/composer/InstalledVersions.php')) {
                include $installedVersionsPath;
            }
        }
        $httpDownloader = self::createHttpDownloader($io, $config);
        $process = new \RectorPrefix20210503\Composer\Util\ProcessExecutor($io);
        $loop = new \RectorPrefix20210503\Composer\Util\Loop($httpDownloader, $process);
        $composer->setLoop($loop);
        // initialize event dispatcher
        $dispatcher = new \RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher($composer, $io, $process);
        $composer->setEventDispatcher($dispatcher);
        // initialize repository manager
        $rm = \RectorPrefix20210503\Composer\Repository\RepositoryFactory::manager($io, $config, $httpDownloader, $dispatcher, $process);
        $composer->setRepositoryManager($rm);
        // force-set the version of the global package if not defined as
        // guessing it adds no value and only takes time
        if (!$fullLoad && !isset($localConfig['version'])) {
            $localConfig['version'] = '1.0.0';
        }
        // load package
        $parser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
        $guesser = new \RectorPrefix20210503\Composer\Package\Version\VersionGuesser($config, $process, $parser);
        $loader = $this->loadRootPackage($rm, $config, $parser, $guesser, $io);
        $package = $loader->load($localConfig, 'RectorPrefix20210503\\Composer\\Package\\RootPackage', $cwd);
        $composer->setPackage($package);
        // load local repository
        $this->addLocalRepository($io, $rm, $vendorDir, $package);
        // initialize installation manager
        $im = $this->createInstallationManager($loop, $io, $dispatcher);
        $composer->setInstallationManager($im);
        if ($fullLoad) {
            // initialize download manager
            $dm = $this->createDownloadManager($io, $config, $httpDownloader, $process, $dispatcher);
            $composer->setDownloadManager($dm);
            // initialize autoload generator
            $generator = new \RectorPrefix20210503\Composer\Autoload\AutoloadGenerator($dispatcher, $io);
            $composer->setAutoloadGenerator($generator);
            // initialize archive manager
            $am = $this->createArchiveManager($config, $dm, $loop);
            $composer->setArchiveManager($am);
        }
        // add installers to the manager (must happen after download manager is created since they read it out of $composer)
        $this->createDefaultInstallers($im, $composer, $io, $process);
        if ($fullLoad) {
            $globalComposer = null;
            if (\realpath($config->get('home')) !== $cwd) {
                $globalComposer = $this->createGlobalComposer($io, $config, $disablePlugins);
            }
            $pm = $this->createPluginManager($io, $composer, $globalComposer, $disablePlugins);
            $composer->setPluginManager($pm);
            $pm->loadInstalledPlugins();
        }
        // init locker if possible
        if ($fullLoad && isset($composerFile)) {
            $lockFile = self::getLockFile($composerFile);
            $locker = new \RectorPrefix20210503\Composer\Package\Locker($io, new \RectorPrefix20210503\Composer\Json\JsonFile($lockFile, null, $io), $im, \file_get_contents($composerFile), $process);
            $composer->setLocker($locker);
        }
        if ($fullLoad) {
            $initEvent = new \RectorPrefix20210503\Composer\EventDispatcher\Event(\RectorPrefix20210503\Composer\Plugin\PluginEvents::INIT);
            $composer->getEventDispatcher()->dispatch($initEvent->getName(), $initEvent);
            // once everything is initialized we can
            // purge packages from local repos if they have been deleted on the filesystem
            if ($rm->getLocalRepository()) {
                $this->purgePackages($rm->getLocalRepository(), $im);
            }
        }
        return $composer;
    }
    /**
     * @param  IOInterface   $io             IO instance
     * @param  bool          $disablePlugins Whether plugins should not be loaded
     * @return Composer|null
     */
    public static function createGlobal(\RectorPrefix20210503\Composer\IO\IOInterface $io, $disablePlugins = \false)
    {
        $factory = new static();
        return $factory->createGlobalComposer($io, static::createConfig($io), $disablePlugins, \true);
    }
    /**
     * @param Repository\RepositoryManager $rm
     * @param string                       $vendorDir
     */
    protected function addLocalRepository(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Repository\RepositoryManager $rm, $vendorDir, \RectorPrefix20210503\Composer\Package\RootPackageInterface $rootPackage)
    {
        $rm->setLocalRepository(new \RectorPrefix20210503\Composer\Repository\InstalledFilesystemRepository(new \RectorPrefix20210503\Composer\Json\JsonFile($vendorDir . '/composer/installed.json', null, $io), \true, $rootPackage));
    }
    /**
     * @param  Config        $config
     * @return Composer|null
     */
    protected function createGlobalComposer(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $disablePlugins, $fullLoad = \false)
    {
        $composer = null;
        try {
            $composer = $this->createComposer($io, $config->get('home') . '/composer.json', $disablePlugins, $config->get('home'), $fullLoad);
        } catch (\Exception $e) {
            $io->writeError('Failed to initialize global composer: ' . $e->getMessage(), \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
        }
        return $composer;
    }
    /**
     * @param  IO\IOInterface             $io
     * @param  Config                     $config
     * @param  EventDispatcher            $eventDispatcher
     * @return Downloader\DownloadManager
     */
    public function createDownloadManager(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Util\HttpDownloader $httpDownloader, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process, \RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher $eventDispatcher = null)
    {
        $cache = null;
        if ($config->get('cache-files-ttl') > 0) {
            $cache = new \RectorPrefix20210503\Composer\Cache($io, $config->get('cache-files-dir'), 'a-z0-9_./');
            $cache->setReadOnly($config->get('cache-read-only'));
        }
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem($process);
        $dm = new \RectorPrefix20210503\Composer\Downloader\DownloadManager($io, \false, $fs);
        switch ($preferred = $config->get('preferred-install')) {
            case 'dist':
                $dm->setPreferDist(\true);
                break;
            case 'source':
                $dm->setPreferSource(\true);
                break;
            case 'auto':
            default:
                // noop
                break;
        }
        if (\is_array($preferred)) {
            $dm->setPreferences($preferred);
        }
        $dm->setDownloader('git', new \RectorPrefix20210503\Composer\Downloader\GitDownloader($io, $config, $process, $fs));
        $dm->setDownloader('svn', new \RectorPrefix20210503\Composer\Downloader\SvnDownloader($io, $config, $process, $fs));
        $dm->setDownloader('fossil', new \RectorPrefix20210503\Composer\Downloader\FossilDownloader($io, $config, $process, $fs));
        $dm->setDownloader('hg', new \RectorPrefix20210503\Composer\Downloader\HgDownloader($io, $config, $process, $fs));
        $dm->setDownloader('perforce', new \RectorPrefix20210503\Composer\Downloader\PerforceDownloader($io, $config, $process, $fs));
        $dm->setDownloader('zip', new \RectorPrefix20210503\Composer\Downloader\ZipDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('rar', new \RectorPrefix20210503\Composer\Downloader\RarDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('tar', new \RectorPrefix20210503\Composer\Downloader\TarDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('gzip', new \RectorPrefix20210503\Composer\Downloader\GzipDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('xz', new \RectorPrefix20210503\Composer\Downloader\XzDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('phar', new \RectorPrefix20210503\Composer\Downloader\PharDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('file', new \RectorPrefix20210503\Composer\Downloader\FileDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        $dm->setDownloader('path', new \RectorPrefix20210503\Composer\Downloader\PathDownloader($io, $config, $httpDownloader, $eventDispatcher, $cache, $fs, $process));
        return $dm;
    }
    /**
     * @param  Config                     $config The configuration
     * @param  Downloader\DownloadManager $dm     Manager use to download sources
     * @return Archiver\ArchiveManager
     */
    public function createArchiveManager(\RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Downloader\DownloadManager $dm, \RectorPrefix20210503\Composer\Util\Loop $loop)
    {
        $am = new \RectorPrefix20210503\Composer\Package\Archiver\ArchiveManager($dm, $loop);
        $am->addArchiver(new \RectorPrefix20210503\Composer\Package\Archiver\ZipArchiver());
        $am->addArchiver(new \RectorPrefix20210503\Composer\Package\Archiver\PharArchiver());
        return $am;
    }
    /**
     * @param  IOInterface          $io
     * @param  Composer             $composer
     * @param  Composer             $globalComposer
     * @param  bool                 $disablePlugins
     * @return Plugin\PluginManager
     */
    protected function createPluginManager(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\Composer $globalComposer = null, $disablePlugins = \false)
    {
        return new \RectorPrefix20210503\Composer\Plugin\PluginManager($io, $composer, $globalComposer, $disablePlugins);
    }
    /**
     * @return Installer\InstallationManager
     */
    public function createInstallationManager(\RectorPrefix20210503\Composer\Util\Loop $loop, \RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher $eventDispatcher = null)
    {
        return new \RectorPrefix20210503\Composer\Installer\InstallationManager($loop, $io, $eventDispatcher);
    }
    /**
     * @param Installer\InstallationManager $im
     * @param Composer                      $composer
     * @param IO\IOInterface                $io
     */
    protected function createDefaultInstallers(\RectorPrefix20210503\Composer\Installer\InstallationManager $im, \RectorPrefix20210503\Composer\Composer $composer, \RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process = null)
    {
        $fs = new \RectorPrefix20210503\Composer\Util\Filesystem($process);
        $binaryInstaller = new \RectorPrefix20210503\Composer\Installer\BinaryInstaller($io, \rtrim($composer->getConfig()->get('bin-dir'), '/'), $composer->getConfig()->get('bin-compat'), $fs);
        $im->addInstaller(new \RectorPrefix20210503\Composer\Installer\LibraryInstaller($io, $composer, null, $fs, $binaryInstaller));
        $im->addInstaller(new \RectorPrefix20210503\Composer\Installer\PluginInstaller($io, $composer, $fs, $binaryInstaller));
        $im->addInstaller(new \RectorPrefix20210503\Composer\Installer\MetapackageInstaller($io));
    }
    /**
     * @param WritableRepositoryInterface   $repo repository to purge packages from
     * @param Installer\InstallationManager $im   manager to check whether packages are still installed
     */
    protected function purgePackages(\RectorPrefix20210503\Composer\Repository\WritableRepositoryInterface $repo, \RectorPrefix20210503\Composer\Installer\InstallationManager $im)
    {
        foreach ($repo->getPackages() as $package) {
            if (!$im->isPackageInstalled($repo, $package)) {
                $repo->removePackage($package);
            }
        }
    }
    protected function loadRootPackage(\RectorPrefix20210503\Composer\Repository\RepositoryManager $rm, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Package\Version\VersionParser $parser, \RectorPrefix20210503\Composer\Package\Version\VersionGuesser $guesser, \RectorPrefix20210503\Composer\IO\IOInterface $io)
    {
        return new \RectorPrefix20210503\Composer\Package\Loader\RootPackageLoader($rm, $config, $parser, $guesser, $io);
    }
    /**
     * @param  IOInterface $io             IO instance
     * @param  mixed       $config         either a configuration array or a filename to read from, if null it will read from
     *                                     the default filename
     * @param  bool        $disablePlugins Whether plugins should not be loaded
     * @return Composer
     */
    public static function create(\RectorPrefix20210503\Composer\IO\IOInterface $io, $config = null, $disablePlugins = \false)
    {
        $factory = new static();
        return $factory->createComposer($io, $config, $disablePlugins);
    }
    /**
     * If you are calling this in a plugin, you probably should instead use $composer->getLoop()->getHttpDownloader()
     *
     * @param  IOInterface    $io      IO instance
     * @param  Config         $config  Config instance
     * @param  array          $options Array of options passed directly to HttpDownloader constructor
     * @return HttpDownloader
     */
    public static function createHttpDownloader(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $options = array())
    {
        static $warned = \false;
        $disableTls = \false;
        // allow running the config command if disable-tls is in the arg list, even if openssl is missing, to allow disabling it via the config command
        if (isset($_SERVER['argv']) && \in_array('disable-tls', $_SERVER['argv']) && (\in_array('conf', $_SERVER['argv']) || \in_array('config', $_SERVER['argv']))) {
            $warned = \true;
            $disableTls = !\extension_loaded('openssl');
        } elseif ($config && $config->get('disable-tls') === \true) {
            if (!$warned) {
                $io->writeError('<warning>You are running Composer with SSL/TLS protection disabled.</warning>');
            }
            $warned = \true;
            $disableTls = \true;
        } elseif (!\extension_loaded('openssl')) {
            throw new \RectorPrefix20210503\Composer\Exception\NoSslException('The openssl extension is required for SSL/TLS protection but is not available. ' . 'If you can not enable the openssl extension, you can disable this error, at your own risk, by setting the \'disable-tls\' option to true.');
        }
        $httpDownloaderOptions = array();
        if ($disableTls === \false) {
            if ($config && $config->get('cafile')) {
                $httpDownloaderOptions['ssl']['cafile'] = $config->get('cafile');
            }
            if ($config && $config->get('capath')) {
                $httpDownloaderOptions['ssl']['capath'] = $config->get('capath');
            }
            $httpDownloaderOptions = \array_replace_recursive($httpDownloaderOptions, $options);
        }
        try {
            $httpDownloader = new \RectorPrefix20210503\Composer\Util\HttpDownloader($io, $config, $httpDownloaderOptions, $disableTls);
        } catch (\RectorPrefix20210503\Composer\Downloader\TransportException $e) {
            if (\false !== \strpos($e->getMessage(), 'cafile')) {
                $io->write('<error>Unable to locate a valid CA certificate file. You must set a valid \'cafile\' option.</error>');
                $io->write('<error>A valid CA certificate file is required for SSL/TLS protection.</error>');
                if (\PHP_VERSION_ID < 50600) {
                    $io->write('<error>It is recommended you upgrade to PHP 5.6+ which can detect your system CA file automatically.</error>');
                }
                $io->write('<error>You can disable this error, at your own risk, by setting the \'disable-tls\' option to true.</error>');
            }
            throw $e;
        }
        return $httpDownloader;
    }
    /**
     * @return bool
     */
    private static function useXdg()
    {
        foreach (\array_keys($_SERVER) as $key) {
            if (\strpos($key, 'XDG_') === 0) {
                return \true;
            }
        }
        if (\RectorPrefix20210503\Composer\Util\Silencer::call('is_dir', '/etc/xdg')) {
            return \true;
        }
        return \false;
    }
    /**
     * @throws \RuntimeException
     * @return string
     */
    private static function getUserDir()
    {
        $home = \getenv('HOME');
        if (!$home) {
            throw new \RuntimeException('The HOME or COMPOSER_HOME environment variable must be set for composer to run correctly');
        }
        return \rtrim(\strtr($home, '\\', '/'), '/');
    }
}
