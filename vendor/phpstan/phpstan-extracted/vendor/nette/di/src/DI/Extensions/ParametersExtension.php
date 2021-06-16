<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Extensions;

use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette;
use RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\DynamicParameter;
/**
 * Parameters.
 */
final class ParametersExtension extends \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\CompilerExtension
{
    /** @var string[] */
    public $dynamicParams = [];
    /** @var string[][] */
    public $dynamicValidators = [];
    /** @var array */
    private $compilerConfig;
    public function __construct(array &$compilerConfig)
    {
        $this->compilerConfig =& $compilerConfig;
    }
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $params = $this->config;
        $resolver = new \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Resolver($builder);
        $generator = new \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\PhpGenerator($builder);
        foreach ($this->dynamicParams as $key) {
            $params[$key] = \array_key_exists($key, $params) ? new \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\DynamicParameter($generator->formatPhp('($this->parameters[?] \\?\\? ?)', $resolver->completeArguments(\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Helpers::filterArguments([$key, $params[$key]])))) : new \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\DynamicParameter(\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\Helpers::format('$this->parameters[?]', $key));
        }
        $builder->parameters = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Helpers::expand($params, $params, \true);
        // expand all except 'services'
        $slice = \array_diff_key($this->compilerConfig, ['services' => 1]);
        $slice = \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Helpers::expand($slice, $builder->parameters);
        $this->compilerConfig = $slice + $this->compilerConfig;
    }
    public function afterCompile(\RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\PhpGenerator\ClassType $class)
    {
        $parameters = $this->getContainerBuilder()->parameters;
        \array_walk_recursive($parameters, function (&$val) {
            if ($val instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Definitions\Statement || $val instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\DynamicParameter) {
                $val = null;
            }
        });
        $cnstr = $class->getMethod('__construct');
        $cnstr->addBody('$this->parameters += ?;', [$parameters]);
        foreach ($this->dynamicValidators as list($param, $expected)) {
            if ($param instanceof \RectorPrefix20210616\_HumbugBox15516bb2b566\Nette\DI\Definitions\Statement) {
                continue;
            }
            $cnstr->addBody('Nette\\Utils\\Validators::assert(?, ?, ?);', [$param, $expected, 'dynamic parameter']);
        }
    }
}
