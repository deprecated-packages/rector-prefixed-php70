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

use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Json\JsonFile;
use RectorPrefix20210503\Composer\Package\Loader\ArrayLoader;
use RectorPrefix20210503\Composer\Package\Loader\LoaderInterface;
use RectorPrefix20210503\Composer\Util\Tar;
use RectorPrefix20210503\Composer\Util\Zip;
/**
 * @author Serge Smertin <serg.smertin@gmail.com>
 */
class ArtifactRepository extends \RectorPrefix20210503\Composer\Repository\ArrayRepository implements \RectorPrefix20210503\Composer\Repository\ConfigurableRepositoryInterface
{
    /** @var LoaderInterface */
    protected $loader;
    protected $lookup;
    protected $repoConfig;
    private $io;
    public function __construct(array $repoConfig, \RectorPrefix20210503\Composer\IO\IOInterface $io)
    {
        parent::__construct();
        if (!\extension_loaded('zip')) {
            throw new \RuntimeException('The artifact repository requires PHP\'s zip extension');
        }
        $this->loader = new \RectorPrefix20210503\Composer\Package\Loader\ArrayLoader();
        $this->lookup = $repoConfig['url'];
        $this->io = $io;
        $this->repoConfig = $repoConfig;
    }
    public function getRepoName()
    {
        return 'artifact repo (' . $this->lookup . ')';
    }
    public function getRepoConfig()
    {
        return $this->repoConfig;
    }
    protected function initialize()
    {
        parent::initialize();
        $this->scanDirectory($this->lookup);
    }
    private function scanDirectory($path)
    {
        $io = $this->io;
        $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\\.(zip|phar|tar|gz|tgz)$/i');
        foreach ($regex as $file) {
            /* @var $file \SplFileInfo */
            if (!$file->isFile()) {
                continue;
            }
            $package = $this->getComposerInformation($file);
            if (!$package) {
                $io->writeError("File <comment>{$file->getBasename()}</comment> doesn't seem to hold a package", \true, \RectorPrefix20210503\Composer\IO\IOInterface::VERBOSE);
                continue;
            }
            $template = 'Found package <info>%s</info> (<comment>%s</comment>) in file <info>%s</info>';
            $io->writeError(\sprintf($template, $package->getName(), $package->getPrettyVersion(), $file->getBasename()), \true, \RectorPrefix20210503\Composer\IO\IOInterface::VERBOSE);
            $this->addPackage($package);
        }
    }
    private function getComposerInformation(\SplFileInfo $file)
    {
        $json = null;
        $fileType = null;
        $fileExtension = \pathinfo($file->getPathname(), \PATHINFO_EXTENSION);
        if (\in_array($fileExtension, array('gz', 'tar', 'tgz'), \true)) {
            $fileType = 'tar';
        } elseif ($fileExtension === 'zip') {
            $fileType = 'zip';
        } else {
            throw new \RuntimeException('Files with "' . $fileExtension . '" extensions aren\'t supported. Only ZIP and TAR/TAR.GZ/TGZ archives are supported.');
        }
        try {
            if ($fileType === 'tar') {
                $json = \RectorPrefix20210503\Composer\Util\Tar::getComposerJson($file->getPathname());
            } else {
                $json = \RectorPrefix20210503\Composer\Util\Zip::getComposerJson($file->getPathname());
            }
        } catch (\Exception $exception) {
            $this->io->write('Failed loading package ' . $file->getPathname() . ': ' . $exception->getMessage(), \false, \RectorPrefix20210503\Composer\IO\IOInterface::VERBOSE);
        }
        if (null === $json) {
            return \false;
        }
        $package = \RectorPrefix20210503\Composer\Json\JsonFile::parseJson($json, $file->getPathname() . '#composer.json');
        $package['dist'] = array('type' => $fileType, 'url' => \strtr($file->getPathname(), '\\', '/'), 'shasum' => \sha1_file($file->getRealPath()));
        try {
            $package = $this->loader->load($package);
        } catch (\UnexpectedValueException $e) {
            throw new \UnexpectedValueException('Failed loading package in ' . $file . ': ' . $e->getMessage(), 0, $e);
        }
        return $package;
    }
}
