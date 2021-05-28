<?php

declare (strict_types=1);
namespace PHPStan\Command;

use PHPStan\Analyser\ResultCache\ResultCacheClearer;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Command\Command;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface;
class ClearResultCacheCommand extends \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Command\Command
{
    const NAME = 'clear-result-cache';
    /** @var string[] */
    private $composerAutoloaderProjectPaths;
    /**
     * @param string[] $composerAutoloaderProjectPaths
     */
    public function __construct(array $composerAutoloaderProjectPaths)
    {
        parent::__construct();
        $this->composerAutoloaderProjectPaths = $composerAutoloaderProjectPaths;
    }
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)->setDescription('Clears the result cache.')->setDefinition([new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputOption('configuration', 'c', \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to project configuration file'), new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputOption('autoload-file', 'a', \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path')]);
    }
    protected function execute(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $autoloadFile = $input->getOption('autoload-file');
        $configuration = $input->getOption('configuration');
        if (!\is_string($autoloadFile) && $autoloadFile !== null || !\is_string($configuration) && $configuration !== null) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        try {
            $inceptionResult = \PHPStan\Command\CommandHelper::begin($input, $output, ['.'], null, null, $autoloadFile, $this->composerAutoloaderProjectPaths, $configuration, null, '0', \false, \false);
        } catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
            return 1;
        }
        $container = $inceptionResult->getContainer();
        /** @var ResultCacheClearer $resultCacheClearer */
        $resultCacheClearer = $container->getByType(\PHPStan\Analyser\ResultCache\ResultCacheClearer::class);
        $path = $resultCacheClearer->clear();
        $output->writeln('<info>Result cache cleared from directory:</info>');
        $output->writeln($path);
        return 0;
    }
}
