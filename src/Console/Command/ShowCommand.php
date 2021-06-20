<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\ShowOutputFormatterCollector;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix20210620\Symfony\Component\Console\Command\Command;
use RectorPrefix20210620\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210620\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210620\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210620\Symplify\PackageBuilder\Console\ShellCode;
final class ShowCommand extends \RectorPrefix20210620\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $outputStyle;
    /**
     * @var \Rector\Core\Console\Output\ShowOutputFormatterCollector
     */
    private $showOutputFormatterCollector;
    /**
     * @var mixed[]
     */
    private $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(\Rector\Core\Contract\Console\OutputStyleInterface $outputStyle, \Rector\Core\Console\Output\ShowOutputFormatterCollector $showOutputFormatterCollector, array $rectors)
    {
        $this->outputStyle = $outputStyle;
        $this->showOutputFormatterCollector = $showOutputFormatterCollector;
        $this->rectors = $rectors;
        parent::__construct();
    }
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Show loaded Rectors with their configuration');
        $names = $this->showOutputFormatterCollector->getNames();
        $description = \sprintf('Select output format: "%s".', \implode('", "', $names));
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT, 'o', \RectorPrefix20210620\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, $description, \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
    }
    protected function execute(\RectorPrefix20210620\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210620\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $outputFormat = (string) $input->getOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT);
        $this->reportLoadedRectors($outputFormat);
        return \RectorPrefix20210620\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
    }
    /**
     * @return void
     */
    private function reportLoadedRectors(string $outputFormat)
    {
        $rectors = \array_filter($this->rectors, function (\Rector\Core\Contract\Rector\RectorInterface $rector) : bool {
            return !$rector instanceof \Rector\PostRector\Contract\Rector\PostRectorInterface;
        });
        $rectorCount = \count($rectors);
        if ($rectorCount === 0) {
            $warningMessage = \sprintf('No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.', \PHP_EOL . \PHP_EOL, \PHP_EOL);
            $this->outputStyle->warning($warningMessage);
            return;
        }
        $outputFormatter = $this->showOutputFormatterCollector->getByName($outputFormat);
        $outputFormatter->list($rectors);
    }
}
