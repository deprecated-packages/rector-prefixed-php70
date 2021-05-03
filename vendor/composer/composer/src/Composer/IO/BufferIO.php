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
namespace RectorPrefix20210503\Composer\IO;

use RectorPrefix20210503\Symfony\Component\Console\Helper\QuestionHelper;
use RectorPrefix20210503\Symfony\Component\Console\Output\StreamOutput;
use RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\StreamableInputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\StringInput;
use RectorPrefix20210503\Symfony\Component\Console\Helper\HelperSet;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class BufferIO extends \RectorPrefix20210503\Composer\IO\ConsoleIO
{
    /**
     * @param string                        $input
     * @param int                           $verbosity
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct($input = '', $verbosity = \RectorPrefix20210503\Symfony\Component\Console\Output\StreamOutput::VERBOSITY_NORMAL, \RectorPrefix20210503\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter = null)
    {
        $input = new \RectorPrefix20210503\Symfony\Component\Console\Input\StringInput($input);
        $input->setInteractive(\false);
        $output = new \RectorPrefix20210503\Symfony\Component\Console\Output\StreamOutput(\fopen('php://memory', 'rw'), $verbosity, $formatter ? $formatter->isDecorated() : \false, $formatter);
        parent::__construct($input, $output, new \RectorPrefix20210503\Symfony\Component\Console\Helper\HelperSet(array(new \RectorPrefix20210503\Symfony\Component\Console\Helper\QuestionHelper())));
    }
    public function getOutput()
    {
        \fseek($this->output->getStream(), 0);
        $output = \stream_get_contents($this->output->getStream());
        $output = \preg_replace_callback("{(?<=^|\n|\10)(.+?)(\10+)}", function ($matches) {
            $pre = \strip_tags($matches[1]);
            if (\strlen($pre) === \strlen($matches[2])) {
                return '';
            }
            // TODO reverse parse the string, skipping span tags and \033\[([0-9;]+)m(.*?)\033\[0m style blobs
            return \rtrim($matches[1]) . "\n";
        }, $output);
        return $output;
    }
    public function setUserInputs(array $inputs)
    {
        if (!$this->input instanceof \RectorPrefix20210503\Symfony\Component\Console\Input\StreamableInputInterface) {
            throw new \RuntimeException('Setting the user inputs requires at least the version 3.2 of the symfony/console component.');
        }
        $this->input->setStream($this->createStream($inputs));
        $this->input->setInteractive(\true);
    }
    private function createStream(array $inputs)
    {
        $stream = \fopen('php://memory', 'r+');
        foreach ($inputs as $input) {
            \fwrite($stream, $input . \PHP_EOL);
        }
        \rewind($stream);
        return $stream;
    }
}
