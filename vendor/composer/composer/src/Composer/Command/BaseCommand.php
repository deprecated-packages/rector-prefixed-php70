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

use RectorPrefix20210503\Composer\Composer;
use RectorPrefix20210503\Composer\Config;
use RectorPrefix20210503\Composer\Console\Application;
use RectorPrefix20210503\Composer\Factory;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\IO\NullIO;
use RectorPrefix20210503\Composer\Plugin\PreCommandRunEvent;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Symfony\Component\Console\Helper\Table;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Command\Command;
/**
 * Base class for Composer commands
 *
 * @method Application getApplication()
 *
 * @author Ryan Weaver <ryan@knplabs.com>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class BaseCommand extends \RectorPrefix20210503\Symfony\Component\Console\Command\Command
{
    /**
     * @var Composer|null
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    /**
     * @param  bool              $required
     * @param  bool|null         $disablePlugins
     * @throws \RuntimeException
     * @return Composer|null
     */
    public function getComposer($required = \true, $disablePlugins = null)
    {
        if (null === $this->composer) {
            $application = $this->getApplication();
            if ($application instanceof \RectorPrefix20210503\Composer\Console\Application) {
                /* @var $application    Application */
                $this->composer = $application->getComposer($required, $disablePlugins);
            } elseif ($required) {
                throw new \RuntimeException('Could not create a Composer\\Composer instance, you must inject ' . 'one if this command is not used with a Composer\\Console\\Application instance');
            }
        }
        return $this->composer;
    }
    /**
     * @param Composer $composer
     */
    public function setComposer(\RectorPrefix20210503\Composer\Composer $composer)
    {
        $this->composer = $composer;
    }
    /**
     * Removes the cached composer instance
     */
    public function resetComposer()
    {
        $this->composer = null;
        $this->getApplication()->resetComposer();
    }
    /**
     * Whether or not this command is meant to call another command.
     *
     * This is mainly needed to avoid duplicated warnings messages.
     *
     * @return bool
     */
    public function isProxyCommand()
    {
        return \false;
    }
    /**
     * @return IOInterface
     */
    public function getIO()
    {
        if (null === $this->io) {
            $application = $this->getApplication();
            if ($application instanceof \RectorPrefix20210503\Composer\Console\Application) {
                /* @var $application    Application */
                $this->io = $application->getIO();
            } else {
                $this->io = new \RectorPrefix20210503\Composer\IO\NullIO();
            }
        }
        return $this->io;
    }
    /**
     * @param IOInterface $io
     */
    public function setIO(\RectorPrefix20210503\Composer\IO\IOInterface $io)
    {
        $this->io = $io;
    }
    /**
     * {@inheritDoc}
     */
    protected function initialize(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        // initialize a plugin-enabled Composer instance, either local or global
        $disablePlugins = $input->hasParameterOption('--no-plugins');
        $composer = $this->getComposer(\false, $disablePlugins);
        if (null === $composer) {
            $composer = \RectorPrefix20210503\Composer\Factory::createGlobal($this->getIO(), $disablePlugins);
        }
        if ($composer) {
            $preCommandRunEvent = new \RectorPrefix20210503\Composer\Plugin\PreCommandRunEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::PRE_COMMAND_RUN, $input, $this->getName());
            $composer->getEventDispatcher()->dispatch($preCommandRunEvent->getName(), $preCommandRunEvent);
        }
        if (\true === $input->hasParameterOption(array('--no-ansi')) && $input->hasOption('no-progress')) {
            $input->setOption('no-progress', \true);
        }
        parent::initialize($input, $output);
    }
    /**
     * Returns preferSource and preferDist values based on the configuration.
     *
     * @param Config         $config
     * @param InputInterface $input
     * @param bool           $keepVcsRequiresPreferSource
     *
     * @return bool[] An array composed of the preferSource and preferDist values
     */
    protected function getPreferredInstallOptions(\RectorPrefix20210503\Composer\Config $config, \RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, $keepVcsRequiresPreferSource = \false)
    {
        $preferSource = \false;
        $preferDist = \false;
        switch ($config->get('preferred-install')) {
            case 'source':
                $preferSource = \true;
                break;
            case 'dist':
                $preferDist = \true;
                break;
            case 'auto':
            default:
                // noop
                break;
        }
        if ($input->getOption('prefer-source') || $input->getOption('prefer-dist') || $keepVcsRequiresPreferSource && $input->hasOption('keep-vcs') && $input->getOption('keep-vcs')) {
            $preferSource = $input->getOption('prefer-source') || $keepVcsRequiresPreferSource && $input->hasOption('keep-vcs') && $input->getOption('keep-vcs');
            $preferDist = (bool) $input->getOption('prefer-dist');
        }
        return array($preferSource, $preferDist);
    }
    protected function formatRequirements(array $requirements)
    {
        $requires = array();
        $requirements = $this->normalizeRequirements($requirements);
        foreach ($requirements as $requirement) {
            if (!isset($requirement['version'])) {
                throw new \UnexpectedValueException('Option ' . $requirement['name'] . ' is missing a version constraint, use e.g. ' . $requirement['name'] . ':^1.0');
            }
            $requires[$requirement['name']] = $requirement['version'];
        }
        return $requires;
    }
    protected function normalizeRequirements(array $requirements)
    {
        $parser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
        return $parser->parseNameVersionPairs($requirements);
    }
    protected function renderTable(array $table, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $renderer = new \RectorPrefix20210503\Symfony\Component\Console\Helper\Table($output);
        $renderer->setStyle('compact');
        $rendererStyle = $renderer->getStyle();
        if (\method_exists($rendererStyle, 'setVerticalBorderChars')) {
            $rendererStyle->setVerticalBorderChars('');
        } else {
            $rendererStyle->setVerticalBorderChar('');
        }
        $rendererStyle->setCellRowContentFormat('%s  ');
        $renderer->setRows($table)->render();
    }
}
