<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210503\JsonSchema\Constraints;

use RectorPrefix20210503\JsonSchema\Entity\JsonPointer;
use RectorPrefix20210503\JsonSchema\Exception\InvalidArgumentException;
use RectorPrefix20210503\JsonSchema\Exception\InvalidSchemaException;
use RectorPrefix20210503\JsonSchema\Exception\RuntimeException;
use RectorPrefix20210503\JsonSchema\Validator;
/**
 * The SchemaConstraint Constraints, validates an element against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class SchemaConstraint extends \RectorPrefix20210503\JsonSchema\Constraints\Constraint
{
    const DEFAULT_SCHEMA_SPEC = 'http://json-schema.org/draft-04/schema#';
    /**
     * {@inheritdoc}
     */
    public function check(&$element, $schema = null, \RectorPrefix20210503\JsonSchema\Entity\JsonPointer $path = null, $i = null)
    {
        if ($schema !== null) {
            // passed schema
            $validationSchema = $schema;
        } elseif ($this->getTypeCheck()->propertyExists($element, $this->inlineSchemaProperty)) {
            // inline schema
            $validationSchema = $this->getTypeCheck()->propertyGet($element, $this->inlineSchemaProperty);
        } else {
            throw new \RectorPrefix20210503\JsonSchema\Exception\InvalidArgumentException('no schema found to verify against');
        }
        // cast array schemas to object
        if (\is_array($validationSchema)) {
            $validationSchema = \RectorPrefix20210503\JsonSchema\Constraints\BaseConstraint::arrayToObjectRecursive($validationSchema);
        }
        // validate schema against whatever is defined in $validationSchema->$schema. If no
        // schema is defined, assume self::DEFAULT_SCHEMA_SPEC (currently draft-04).
        if ($this->factory->getConfig(self::CHECK_MODE_VALIDATE_SCHEMA)) {
            if (!$this->getTypeCheck()->isObject($validationSchema)) {
                throw new \RectorPrefix20210503\JsonSchema\Exception\RuntimeException('Cannot validate the schema of a non-object');
            }
            if ($this->getTypeCheck()->propertyExists($validationSchema, '$schema')) {
                $schemaSpec = $this->getTypeCheck()->propertyGet($validationSchema, '$schema');
            } else {
                $schemaSpec = self::DEFAULT_SCHEMA_SPEC;
            }
            // get the spec schema
            $schemaStorage = $this->factory->getSchemaStorage();
            if (!$this->getTypeCheck()->isObject($schemaSpec)) {
                $schemaSpec = $schemaStorage->getSchema($schemaSpec);
            }
            // save error count, config & subtract CHECK_MODE_VALIDATE_SCHEMA
            $initialErrorCount = $this->numErrors();
            $initialConfig = $this->factory->getConfig();
            $initialContext = $this->factory->getErrorContext();
            $this->factory->removeConfig(self::CHECK_MODE_VALIDATE_SCHEMA | self::CHECK_MODE_APPLY_DEFAULTS);
            $this->factory->addConfig(self::CHECK_MODE_TYPE_CAST);
            $this->factory->setErrorContext(\RectorPrefix20210503\JsonSchema\Validator::ERROR_SCHEMA_VALIDATION);
            // validate schema
            try {
                $this->check($validationSchema, $schemaSpec);
            } catch (\Exception $e) {
                if ($this->factory->getConfig(self::CHECK_MODE_EXCEPTIONS)) {
                    throw new \RectorPrefix20210503\JsonSchema\Exception\InvalidSchemaException('Schema did not pass validation', 0, $e);
                }
            }
            if ($this->numErrors() > $initialErrorCount) {
                $this->addError($path, 'Schema is not valid', 'schema');
            }
            // restore the initial config
            $this->factory->setConfig($initialConfig);
            $this->factory->setErrorContext($initialContext);
        }
        // validate element against $validationSchema
        $this->checkUndefined($element, $validationSchema, $path, $i);
    }
}
