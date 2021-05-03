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
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class AboutCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('about')->setDescription('Shows the short information about Composer.')->setHelp(<<<EOT
<info>php composer.phar about</info>
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->getIO()->write(<<<EOT
<info>Composer - Dependency Manager for PHP</info>
<comment>Composer is a dependency manager tracking local dependencies of your projects and libraries.
See https://getcomposer.org/ for more information.</comment>
EOT
);
        return 0;
    }
}
