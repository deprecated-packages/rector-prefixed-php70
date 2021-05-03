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
use RectorPrefix20210503\Composer\DependencyResolver\Request;
use RectorPrefix20210503\Composer\Installer;
use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Plugin\CommandEvent;
use RectorPrefix20210503\Composer\Plugin\PluginEvents;
use RectorPrefix20210503\Composer\Package\Version\VersionParser;
use RectorPrefix20210503\Composer\Util\HttpDownloader;
use RectorPrefix20210503\Composer\Semver\Constraint\MultiConstraint;
use RectorPrefix20210503\Composer\Package\Link;
use RectorPrefix20210503\Symfony\Component\Console\Helper\Table;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210503\Symfony\Component\Console\Question\Question;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Nils Adermann <naderman@naderman.de>
 */
class UpdateCommand extends \RectorPrefix20210503\Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('update')->setAliases(array('u', 'upgrade'))->setDescription('Upgrades your dependencies to the latest version according to composer.json, and updates the composer.lock file.')->setDefinition(array(new \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument('packages', \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Packages that should be updated, if not provided all packages are.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('with', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY | \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Temporary version constraint to add, e.g. foo/bar:1.0.0 or foo/bar=1.0.0'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-source', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Forces installation from package sources when possible, including VCS information.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-dist', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Forces installation from package dist even for dev versions.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dry-run', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Outputs the operations but will not execute anything (implicitly enables --verbose).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'DEPRECATED: Enables installation of require-dev packages (enabled by default, only present for BC).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-dev', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Disables installation of require-dev packages.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('lock', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Overwrites the lock file hash to suppress warning about the lock file being out of date without updating package versions. Package metadata like mirrors and URLs are updated if they changed.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-install', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Skip the install step after updating the composer.lock file.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-autoloader', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Skips autoloader generation'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-scripts', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Skips the execution of all scripts defined in composer.json file.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-suggest', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'DEPRECATED: This flag does not exist anymore.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('no-progress', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Do not output download progress.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('with-dependencies', 'w', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Update also dependencies of packages in the argument list, except those which are root requirements.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('with-all-dependencies', 'W', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Update also dependencies of packages in the argument list, including those which are root requirements.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('verbose', 'v|vv|vvv', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Shows more details including new commits pulled in when updating packages.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('optimize-autoloader', 'o', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Optimize autoloader during autoloader dump.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('classmap-authoritative', 'a', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Autoload classes from the classmap only. Implicitly enables `--optimize-autoloader`.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('apcu-autoloader', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Use APCu to cache found/not-found classes.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('apcu-autoloader-prefix', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Use a custom prefix for the APCu autoloader cache. Implicitly enables --apcu-autoloader'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-req', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED | \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY, 'Ignore a specific platform requirement (php & ext- packages).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('ignore-platform-reqs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Ignore all platform requirements (php & ext- packages).'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-stable', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Prefer stable versions of dependencies.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('prefer-lowest', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Prefer lowest versions of dependencies.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('interactive', 'i', \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Interactive interface with autocompletion to select the packages to update.'), new \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption('root-reqs', null, \RectorPrefix20210503\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Restricts the update to your first degree dependencies.')))->setHelp(<<<EOT
The <info>update</info> command reads the composer.json file from the
current directory, processes it, and updates, removes or installs all the
dependencies.

<info>php composer.phar update</info>

To limit the update operation to a few packages, you can list the package(s)
you want to update as such:

<info>php composer.phar update vendor/package1 foo/mypackage [...]</info>

You may also use an asterisk (*) pattern to limit the update operation to package(s)
from a specific vendor:

<info>php composer.phar update vendor/package1 foo/* [...]</info>

To run an update with more restrictive constraints you can use:

<info>php composer.phar update --with vendor/package:1.0.*</info>

To run a partial update with more restrictive constraints you can use the shorthand:

<info>php composer.phar update vendor/package:1.0.*</info>

To select packages names interactively with auto-completion use <info>-i</info>.

Read more at https://getcomposer.org/doc/03-cli.md#update-u
EOT
);
    }
    protected function execute(\RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $io = $this->getIO();
        if ($input->getOption('dev')) {
            $io->writeError('<warning>You are using the deprecated option "--dev". It has no effect and will break in Composer 3.</warning>');
        }
        if ($input->getOption('no-suggest')) {
            $io->writeError('<warning>You are using the deprecated option "--no-suggest". It has no effect and will break in Composer 3.</warning>');
        }
        $composer = $this->getComposer(\true, $input->getOption('no-plugins'));
        if (!\RectorPrefix20210503\Composer\Util\HttpDownloader::isCurlEnabled()) {
            $io->writeError('<warning>Composer is operating significantly slower than normal because you do not have the PHP curl extension enabled.</warning>');
        }
        $packages = $input->getArgument('packages');
        $reqs = $this->formatRequirements($input->getOption('with'));
        // extract --with shorthands from the allowlist
        if ($packages) {
            $allowlistPackagesWithRequirements = \array_filter($packages, function ($pkg) {
                return \preg_match('{\\S+[ =:]\\S+}', $pkg) > 0;
            });
            foreach ($this->formatRequirements($allowlistPackagesWithRequirements) as $package => $constraint) {
                $reqs[$package] = $constraint;
            }
            // replace the foo/bar:req by foo/bar in the allowlist
            foreach ($allowlistPackagesWithRequirements as $package) {
                $packageName = \preg_replace('{^([^ =:]+)[ =:].*$}', '$1', $package);
                $index = \array_search($package, $packages);
                $packages[$index] = $packageName;
            }
        }
        $rootRequires = $composer->getPackage()->getRequires();
        $rootDevRequires = $composer->getPackage()->getDevRequires();
        foreach ($reqs as $package => $constraint) {
            if (isset($rootRequires[$package])) {
                $rootRequires[$package] = $this->appendConstraintToLink($rootRequires[$package], $constraint);
            } elseif (isset($rootDevRequires[$package])) {
                $rootDevRequires[$package] = $this->appendConstraintToLink($rootDevRequires[$package], $constraint);
            } else {
                throw new \UnexpectedValueException('Only root package requirements can receive temporary constraints and ' . $package . ' is not one');
            }
        }
        $composer->getPackage()->setRequires($rootRequires);
        $composer->getPackage()->setDevRequires($rootDevRequires);
        if ($input->getOption('interactive')) {
            $packages = $this->getPackagesInteractively($io, $input, $output, $composer, $packages);
        }
        if ($input->getOption('root-reqs')) {
            $requires = \array_keys($rootRequires);
            if (!$input->getOption('no-dev')) {
                $requires = \array_merge($requires, \array_keys($rootDevRequires));
            }
            if (!empty($packages)) {
                $packages = \array_intersect($packages, $requires);
            } else {
                $packages = $requires;
            }
        }
        // the arguments lock/nothing/mirrors are not package names but trigger a mirror update instead
        // they are further mutually exclusive with listing actual package names
        $filteredPackages = \array_filter($packages, function ($package) {
            return !\in_array($package, array('lock', 'nothing', 'mirrors'), \true);
        });
        $updateMirrors = $input->getOption('lock') || \count($filteredPackages) != \count($packages);
        $packages = $filteredPackages;
        if ($updateMirrors && !empty($packages)) {
            $io->writeError('<error>You cannot simultaneously update only a selection of packages and regenerate the lock file metadata.</error>');
            return -1;
        }
        $commandEvent = new \RectorPrefix20210503\Composer\Plugin\CommandEvent(\RectorPrefix20210503\Composer\Plugin\PluginEvents::COMMAND, 'update', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $composer->getInstallationManager()->setOutputProgress(!$input->getOption('no-progress'));
        $install = \RectorPrefix20210503\Composer\Installer::create($io, $composer);
        $config = $composer->getConfig();
        list($preferSource, $preferDist) = $this->getPreferredInstallOptions($config, $input);
        $optimize = $input->getOption('optimize-autoloader') || $config->get('optimize-autoloader');
        $authoritative = $input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
        $apcuPrefix = $input->getOption('apcu-autoloader-prefix');
        $apcu = $apcuPrefix !== null || $input->getOption('apcu-autoloader') || $config->get('apcu-autoloader');
        $updateAllowTransitiveDependencies = \RectorPrefix20210503\Composer\DependencyResolver\Request::UPDATE_ONLY_LISTED;
        if ($input->getOption('with-all-dependencies')) {
            $updateAllowTransitiveDependencies = \RectorPrefix20210503\Composer\DependencyResolver\Request::UPDATE_LISTED_WITH_TRANSITIVE_DEPS;
        } elseif ($input->getOption('with-dependencies')) {
            $updateAllowTransitiveDependencies = \RectorPrefix20210503\Composer\DependencyResolver\Request::UPDATE_LISTED_WITH_TRANSITIVE_DEPS_NO_ROOT_REQUIRE;
        }
        $ignorePlatformReqs = $input->getOption('ignore-platform-reqs') ?: ($input->getOption('ignore-platform-req') ?: \false);
        $install->setDryRun($input->getOption('dry-run'))->setVerbose($input->getOption('verbose'))->setPreferSource($preferSource)->setPreferDist($preferDist)->setDevMode(!$input->getOption('no-dev'))->setDumpAutoloader(!$input->getOption('no-autoloader'))->setRunScripts(!$input->getOption('no-scripts'))->setOptimizeAutoloader($optimize)->setClassMapAuthoritative($authoritative)->setApcuAutoloader($apcu, $apcuPrefix)->setUpdate(\true)->setInstall(!$input->getOption('no-install'))->setUpdateMirrors($updateMirrors)->setUpdateAllowList($packages)->setUpdateAllowTransitiveDependencies($updateAllowTransitiveDependencies)->setIgnorePlatformRequirements($ignorePlatformReqs)->setPreferStable($input->getOption('prefer-stable'))->setPreferLowest($input->getOption('prefer-lowest'));
        if ($input->getOption('no-plugins')) {
            $install->disablePlugins();
        }
        return $install->run();
    }
    private function getPackagesInteractively(\RectorPrefix20210503\Composer\IO\IOInterface $io, \RectorPrefix20210503\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210503\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210503\Composer\Composer $composer, array $packages)
    {
        if (!$input->isInteractive()) {
            throw new \InvalidArgumentException('--interactive cannot be used in non-interactive terminals.');
        }
        $requires = \array_merge($composer->getPackage()->getRequires(), $composer->getPackage()->getDevRequires());
        $autocompleterValues = array();
        foreach ($requires as $require) {
            $target = $require->getTarget();
            $autocompleterValues[\strtolower($target)] = $target;
        }
        $installedPackages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        foreach ($installedPackages as $package) {
            $autocompleterValues[$package->getName()] = $package->getPrettyName();
        }
        $helper = $this->getHelper('question');
        $question = new \RectorPrefix20210503\Symfony\Component\Console\Question\Question('<comment>Enter package name: </comment>', null);
        $io->writeError('<info>Press enter without value to end submission</info>');
        do {
            $autocompleterValues = \array_diff($autocompleterValues, $packages);
            $question->setAutocompleterValues($autocompleterValues);
            $addedPackage = $helper->ask($input, $output, $question);
            if (!\is_string($addedPackage) || empty($addedPackage)) {
                break;
            }
            $addedPackage = \strtolower($addedPackage);
            if (!\in_array($addedPackage, $packages)) {
                $packages[] = $addedPackage;
            }
        } while (\true);
        $packages = \array_filter($packages);
        if (!$packages) {
            throw new \InvalidArgumentException('You must enter minimum one package.');
        }
        $table = new \RectorPrefix20210503\Symfony\Component\Console\Helper\Table($output);
        $table->setHeaders(array('Selected packages'));
        foreach ($packages as $package) {
            $table->addRow(array($package));
        }
        $table->render();
        if ($io->askConfirmation(\sprintf('Would you like to continue and update the above package%s [<comment>yes</comment>]? ', 1 === \count($packages) ? '' : 's'))) {
            return $packages;
        }
        throw new \RuntimeException('Installation aborted.');
    }
    private function appendConstraintToLink(\RectorPrefix20210503\Composer\Package\Link $link, $constraint)
    {
        $parser = new \RectorPrefix20210503\Composer\Package\Version\VersionParser();
        $oldPrettyString = $link->getConstraint()->getPrettyString();
        $newConstraint = \RectorPrefix20210503\Composer\Semver\Constraint\MultiConstraint::create(array($link->getConstraint(), $parser->parseConstraints($constraint)));
        $newConstraint->setPrettyString($oldPrettyString . ', ' . $constraint);
        return new \RectorPrefix20210503\Composer\Package\Link($link->getSource(), $link->getTarget(), $newConstraint, $link->getDescription(), $link->getPrettyConstraint() . ', ' . $constraint);
    }
}
