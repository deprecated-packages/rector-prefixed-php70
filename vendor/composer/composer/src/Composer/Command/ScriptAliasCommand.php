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

use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ScriptAliasCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    private $script;
    private $description;
    public function __construct($script, $description)
    {
        $this->script = $script;
        $this->description = empty($description) ? 'Runs the ' . $script . ' script as defined in composer.json.' : $description;
        parent::__construct();
    }
    protected function configure()
    {
        $this->setName($this->script)->setDescription($this->description)->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Sets the dev mode.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables the dev mode.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('args', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, '')))->setHelp(<<<EOT
The <info>run-script</info> command runs scripts defined in composer.json:

<info>php composer.phar run-script post-update-cmd</info>

Read more at https://getcomposer.org/doc/03-cli.md#run-script
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $composer = $this->getComposer();
        $args = $input->getArguments();
        return $composer->getEventDispatcher()->dispatchScript($this->script, $input->getOption('dev') || !$input->getOption('no-dev'), $args['args']);
    }
}
