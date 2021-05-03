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

use RectorPrefix20210503\Composer\Package\Link;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Package\RootPackage;
use RectorPrefix20210503\Composer\Repository\InstalledArrayRepository;
use RectorPrefix20210503\Composer\Repository\CompositeRepository;
use RectorPrefix20210503\Composer\Repository\RootPackageRepository;
use RectorPrefix20210503\Composer\Repository\InstalledRepository;
use RectorPrefix20210503\Composer\Repository\PlatformRepository;
use RectorPrefix20210503\Composer\Repository\RepositoryFactory;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterStyle;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base implementation for commands mapping dependency relationships.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class BaseDependencyCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    const ARGUMENT_PACKAGE = 'package';
    const ARGUMENT_CONSTRAINT = 'constraint';
    const OPTION_RECURSIVE = 'recursive';
    const OPTION_TREE = 'tree';
    protected $colors;
    /**
     * Set common options and arguments.
     */
    protected function configure()
    {
        $this->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument(self::ARGUMENT_PACKAGE, \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Package to inspect'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument(self::ARGUMENT_CONSTRAINT, \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Optional version constraint', '*'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption(self::OPTION_RECURSIVE, 'r', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Recursively resolves up to the root package'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption(self::OPTION_TREE, 't', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Prints the results as a nested tree')));
    }
    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @param  bool            $inverted Whether to invert matching process (why-not vs why behaviour)
     * @return int             Exit code of the operation.
     */
    protected function doExecute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output, $inverted = \false)
    {
        // Emit command event on startup
        $composer = $this->getComposer();
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, $this->getName(), $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $platformOverrides = $composer->getConfig()->get('platform') ?: array();
        $installedRepo = new \RectorPrefix20210503\Composer\Repository\InstalledRepository(array(new \RectorPrefix20210503\Composer\Repository\RootPackageRepository($composer->getPackage()), $composer->getRepositoryManager()->getLocalRepository(), new \RectorPrefix20210503\Composer\Repository\PlatformRepository(array(), $platformOverrides)));
        // Parse package name and constraint
        list($needle, $textConstraint) = \array_pad(\explode(':', $input->getArgument(self::ARGUMENT_PACKAGE)), 2, $input->getArgument(self::ARGUMENT_CONSTRAINT));
        // Find packages that are or provide the requested package first
        $packages = $installedRepo->findPackagesWithReplacersAndProviders($needle);
        if (empty($packages)) {
            throw new \InvalidArgumentException(\sprintf('Could not find package "%s" in your project', $needle));
        }
        // If the version we ask for is not installed then we need to locate it in remote repos and add it.
        // This is needed for why-not to resolve conflicts from an uninstalled version against installed packages.
        if (!$installedRepo->findPackage($needle, $textConstraint)) {
            $defaultRepos = new \RectorPrefix20210503\Composer\Repository\CompositeRepository(\RectorPrefix20210503\Composer\Repository\RepositoryFactory::defaultRepos($this->getIO()));
            if ($match = $defaultRepos->findPackage($needle, $textConstraint)) {
                $installedRepo->addRepository(new \RectorPrefix20210503\Composer\Repository\InstalledArrayRepository(array(clone $match)));
            }
        }
        // Include replaced packages for inverted lookups as they are then the actual starting point to consider
        $needles = array($needle);
        if ($inverted) {
            foreach ($packages as $package) {
                $needles = \array_merge($needles, \array_map(function (\RectorPrefix20210503\Composer\Package\Link $link) {
                    return $link->getTarget();
                }, $package->getReplaces()));
            }
        }
        // Parse constraint if one was supplied
        if ('*' !== $textConstraint) {
            $versionParser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
            $constraint = $versionParser->parseConstraints($textConstraint);
        } else {
            $constraint = null;
        }
        // Parse rendering options
        $renderTree = $input->getOption(self::OPTION_TREE);
        $recursive = $renderTree || $input->getOption(self::OPTION_RECURSIVE);
        // Resolve dependencies
        $results = $installedRepo->getDependents($needles, $constraint, $inverted, $recursive);
        if (empty($results)) {
            $extra = null !== $constraint ? \sprintf(' in versions %smatching %s', $inverted ? 'not ' : '', $textConstraint) : '';
            $this->getIO()->writeError(\sprintf('<info>There is no installed package depending on "%s"%s</info>', $needle, $extra));
        } elseif ($renderTree) {
            $this->initStyles($output);
            $root = $packages[0];
            $this->getIO()->write(\sprintf('<info>%s</info> %s %s', $root->getPrettyName(), $root->getPrettyVersion(), $root->getDescription()));
            $this->printTree($results);
        } else {
            $this->printTable($output, $results);
        }
        return 0;
    }
    /**
     * Assembles and prints a bottom-up table of the dependencies.
     *
     * @param OutputInterface $output
     * @param array           $results
     */
    protected function printTable(\RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output, $results)
    {
        $table = array();
        $doubles = array();
        do {
            $queue = array();
            $rows = array();
            foreach ($results as $result) {
                /**
                 * @var PackageInterface $package
                 * @var Link             $link
                 */
                list($package, $link, $children) = $result;
                $unique = (string) $link;
                if (isset($doubles[$unique])) {
                    continue;
                }
                $doubles[$unique] = \true;
                $version = $package->getPrettyVersion() === \RectorPrefix20210503\Composer\Package\RootPackage::DEFAULT_PRETTY_VERSION ? '-' : $package->getPrettyVersion();
                $rows[] = array($package->getPrettyName(), $version, $link->getDescription(), \sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint()));
                if ($children) {
                    $queue = \array_merge($queue, $children);
                }
            }
            $results = $queue;
            $table = \array_merge($rows, $table);
        } while (!empty($results));
        $this->renderTable($table, $output);
    }
    /**
     * Init styles for tree
     *
     * @param OutputInterface $output
     */
    protected function initStyles(\RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->colors = array('green', 'yellow', 'cyan', 'magenta', 'blue');
        foreach ($this->colors as $color) {
            $style = new \RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterStyle($color);
            $output->getFormatter()->setStyle($color, $style);
        }
    }
    /**
     * Recursively prints a tree of the selected results.
     *
     * @param array  $results Results to be printed at this level.
     * @param string $prefix  Prefix of the current tree level.
     * @param int    $level   Current level of recursion.
     */
    protected function printTree($results, $prefix = '', $level = 1)
    {
        $count = \count($results);
        $idx = 0;
        foreach ($results as $result) {
            /**
             * @var PackageInterface $package
             * @var Link             $link
             * @var array|bool       $children
             */
            list($package, $link, $children) = $result;
            $color = $this->colors[$level % \count($this->colors)];
            $prevColor = $this->colors[($level - 1) % \count($this->colors)];
            $isLast = ++$idx == $count;
            $versionText = $package->getPrettyVersion() === \RectorPrefix20210503\Composer\Package\RootPackage::DEFAULT_PRETTY_VERSION ? '' : $package->getPrettyVersion();
            $packageText = \rtrim(\sprintf('<%s>%s</%1$s> %s', $color, $package->getPrettyName(), $versionText));
            $linkText = \sprintf('%s <%s>%s</%2$s> %s', $link->getDescription(), $prevColor, $link->getTarget(), $link->getPrettyConstraint());
            $circularWarn = $children === \false ? '(circular dependency aborted here)' : '';
            $this->writeTreeLine(\rtrim(\sprintf("%s%s%s (%s) %s", $prefix, $isLast ? '└──' : '├──', $packageText, $linkText, $circularWarn)));
            if ($children) {
                $this->printTree($children, $prefix . ($isLast ? '   ' : '│  '), $level + 1);
            }
        }
    }
    private function writeTreeLine($line)
    {
        $io = $this->getIO();
        if (!$io->isDecorated()) {
            $line = \str_replace(array('└', '├', '──', '│'), array('`-', '|-', '-', '|'), $line);
        }
        $io->write($line);
    }
}
