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

use RectorPrefix20210503\Composer\Factory;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Composer\Repository\CompositeRepository;
use RectorPrefix20210503\Composer\Repository\PlatformRepository;
use RectorPrefix20210503\Composer\Repository\RepositoryInterface;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
/**
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class SearchCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected $matches;
    protected $lowMatches = array();
    protected $tokens;
    protected $output;
    protected $onlyName;
    protected function configure()
    {
        $this->setName('search')->setDescription('Searches for packages.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('only-name', 'N', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Search only in name'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('type', 't', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Search for a specific package type'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('tokens', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::REQUIRED, 'tokens to search for')))->setHelp(<<<EOT
The search command searches for packages by its name
<info>php composer.phar search symfony composer</info>

Read more at https://getcomposer.org/doc/03-cli.md#search
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        // init repos
        $platformRepo = new \RectorPrefix20210503\Composer\Repository\PlatformRepository();
        $io = $this->getIO();
        if (!($composer = $this->getComposer(\false))) {
            $composer = \RectorPrefix20210503\Composer\Factory::create($this->getIO(), array(), $input->hasParameterOption('--no-plugins'));
        }
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $installedRepo = new \RectorPrefix20210503\Composer\Repository\CompositeRepository(array($localRepo, $platformRepo));
        $repos = new \RectorPrefix20210503\Composer\Repository\CompositeRepository(\array_merge(array($installedRepo), $composer->getRepositoryManager()->getRepositories()));
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'search', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $onlyName = $input->getOption('only-name');
        $type = $input->getOption('type') ?: null;
        $flags = $onlyName ? \RectorPrefix20210503\Composer\Repository\RepositoryInterface::SEARCH_NAME : \RectorPrefix20210503\Composer\Repository\RepositoryInterface::SEARCH_FULLTEXT;
        $results = $repos->search(\implode(' ', $input->getArgument('tokens')), $flags, $type);
        foreach ($results as $result) {
            $io->write($result['name'] . (isset($result['description']) ? ' ' . $result['description'] : ''));
        }
        return 0;
    }
}
