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
namespace RectorPrefix20210503\Composer\Repository;

use RectorPrefix20210503\Composer\Factory;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher;
use RectorPrefix20210503\Composer\Util\HttpDownloader;
use RectorPrefix20210503\Composer\Util\ProcessExecutor;
use RectorPrefix20210503\Composer\Json\JsonFile;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class RepositoryFactory
{
    /**
     * @param  IOInterface $io
     * @param  Config      $config
     * @param  string      $repository
     * @param  bool        $allowFilesystem
     * @return array|mixed
     */
    public static function configFromString(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $repository, $allowFilesystem = \false)
    {
        if (0 === \strpos($repository, 'http')) {
            $repoConfig = array('type' => 'composer', 'url' => $repository);
        } elseif ("json" === \pathinfo($repository, \PATHINFO_EXTENSION)) {
            $json = new \RectorPrefix20210503\Composer\Json\JsonFile($repository, \RectorPrefix20210503\Composer\Factory::createHttpDownloader($io, $config));
            $data = $json->read();
            if (!empty($data['packages']) || !empty($data['includes']) || !empty($data['provider-includes'])) {
                $repoConfig = array('type' => 'composer', 'url' => 'file://' . \strtr(\realpath($repository), '\\', '/'));
            } elseif ($allowFilesystem) {
                $repoConfig = array('type' => 'filesystem', 'json' => $json);
            } else {
                throw new \InvalidArgumentException("Invalid repository URL ({$repository}) given. This file does not contain a valid composer repository.");
            }
        } elseif (\strpos($repository, '{') === 0) {
            // assume it is a json object that makes a repo config
            $repoConfig = \RectorPrefix20210503\Composer\Json\JsonFile::parseJson($repository);
        } else {
            throw new \InvalidArgumentException("Invalid repository url ({$repository}) given. Has to be a .json file, an http url or a JSON object.");
        }
        return $repoConfig;
    }
    /**
     * @param  IOInterface         $io
     * @param  Config              $config
     * @param  string              $repository
     * @param  bool                $allowFilesystem
     * @return RepositoryInterface
     */
    public static function fromString(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, $repository, $allowFilesystem = \false, \RectorPrefix20210503\Composer\Repository\RepositoryManager $rm = null)
    {
        $repoConfig = static::configFromString($io, $config, $repository, $allowFilesystem);
        return static::createRepo($io, $config, $repoConfig, $rm);
    }
    /**
     * @param  IOInterface         $io
     * @param  Config              $config
     * @param  array               $repoConfig
     * @return RepositoryInterface
     */
    public static function createRepo(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, array $repoConfig, \RectorPrefix20210503\Composer\Repository\RepositoryManager $rm = null)
    {
        if (!$rm) {
            $rm = static::manager($io, $config, \RectorPrefix20210503\Composer\Factory::createHttpDownloader($io, $config));
        }
        $repos = static::createRepos($rm, array($repoConfig));
        return \reset($repos);
    }
    /**
     * @param  IOInterface|null       $io
     * @param  Config|null            $config
     * @param  RepositoryManager|null $rm
     * @return RepositoryInterface[]
     */
    public static function defaultRepos(\RectorPrefix20210503\Composer\IO\IOInterface $io = null, \RectorPrefix20210503\Composer\Config $config = null, \RectorPrefix20210503\Composer\Repository\RepositoryManager $rm = null)
    {
        if (!$config) {
            $config = \RectorPrefix20210503\Composer\Factory::createConfig($io);
        }
        if ($io) {
            $io->loadConfiguration($config);
        }
        if (!$rm) {
            if (!$io) {
                throw new \InvalidArgumentException('This function requires either an IOInterface or a RepositoryManager');
            }
            $rm = static::manager($io, $config, \RectorPrefix20210503\Composer\Factory::createHttpDownloader($io, $config));
        }
        return static::createRepos($rm, $config->getRepositories());
    }
    /**
     * @param  IOInterface       $io
     * @param  Config            $config
     * @param  EventDispatcher   $eventDispatcher
     * @param  HttpDownloader    $httpDownloader
     * @return RepositoryManager
     */
    public static function manager(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Composer\Util\HttpDownloader $httpDownloader, \RectorPrefix20210503\Composer\EventDispatcher\EventDispatcher $eventDispatcher = null, \RectorPrefix20210503\Composer\Util\ProcessExecutor $process = null)
    {
        $rm = new \RectorPrefix20210503\Composer\Repository\RepositoryManager($io, $config, $httpDownloader, $eventDispatcher, $process);
        $rm->setRepositoryClass('composer', 'RectorPrefix20210503\\Composer\\Repository\\ComposerRepository');
        $rm->setRepositoryClass('vcs', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('package', 'RectorPrefix20210503\\Composer\\Repository\\PackageRepository');
        $rm->setRepositoryClass('pear', 'RectorPrefix20210503\\Composer\\Repository\\PearRepository');
        $rm->setRepositoryClass('git', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('git-bitbucket', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('github', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('gitlab', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('svn', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('fossil', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('perforce', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('hg', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('hg-bitbucket', 'RectorPrefix20210503\\Composer\\Repository\\VcsRepository');
        $rm->setRepositoryClass('artifact', 'RectorPrefix20210503\\Composer\\Repository\\ArtifactRepository');
        $rm->setRepositoryClass('path', 'RectorPrefix20210503\\Composer\\Repository\\PathRepository');
        return $rm;
    }
    /**
     * @return RepositoryInterface[]
     */
    private static function createRepos(\RectorPrefix20210503\Composer\Repository\RepositoryManager $rm, array $repoConfigs)
    {
        $repos = array();
        foreach ($repoConfigs as $index => $repo) {
            if (\is_string($repo)) {
                throw new \UnexpectedValueException('"repositories" should be an array of repository definitions, only a single repository was given');
            }
            if (!\is_array($repo)) {
                throw new \UnexpectedValueException('Repository "' . $index . '" (' . \json_encode($repo) . ') should be an array, ' . \gettype($repo) . ' given');
            }
            if (!isset($repo['type'])) {
                throw new \UnexpectedValueException('Repository "' . $index . '" (' . \json_encode($repo) . ') must have a type defined');
            }
            $name = self::generateRepositoryName($index, $repo, $repos);
            if ($repo['type'] === 'filesystem') {
                $repos[$name] = new \RectorPrefix20210503\Composer\Repository\FilesystemRepository($repo['json']);
            } else {
                $repos[$name] = $rm->createRepository($repo['type'], $repo, $index);
            }
        }
        return $repos;
    }
    public static function generateRepositoryName($index, array $repo, array $existingRepos)
    {
        $name = \is_int($index) && isset($repo['url']) ? \preg_replace('{^https?://}i', '', $repo['url']) : $index;
        while (isset($existingRepos[$name])) {
            $name .= '2';
        }
        return $name;
    }
}
