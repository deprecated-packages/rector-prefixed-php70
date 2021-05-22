<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210522\JsonSchema;

use RectorPrefix20210522\JsonSchema\Constraints\BaseConstraint;
use RectorPrefix20210522\JsonSchema\Constraints\Constraint;
/**
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 *
 * @see    README.md
 */
class Validator extends \RectorPrefix20210522\JsonSchema\Constraints\BaseConstraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';
    const ERROR_NONE = 0x0;
    const ERROR_ALL = 0xffffffff;
    const ERROR_DOCUMENT_VALIDATION = 0x1;
    const ERROR_SCHEMA_VALIDATION = 0x2;
    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org.
     *
     * Note that the first argument is passed by reference, so you must pass in a variable.
     */
    public function validate(&$value, $schema = null, $checkMode = null)
    {
        // make sure $schema is an object
        if (\is_array($schema)) {
            $schema = self::arrayToObjectRecursive($schema);
        }
        // set checkMode
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }
        // add provided schema to SchemaStorage with internal URI to allow internal $ref resolution
        if (\is_object($schema) && \property_exists($schema, 'id')) {
            $schemaURI = $schema->id;
        } else {
            $schemaURI = \RectorPrefix20210522\JsonSchema\SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        }
        $this->factory->getSchemaStorage()->addSchema($schemaURI, $schema);
        $validator = $this->factory->createInstanceFor('schema');
        $validator->check($value, $this->factory->getSchemaStorage()->getSchema($schemaURI));
        $this->factory->setConfig($initialCheckMode);
        $this->addErrors(\array_unique($validator->getErrors(), \SORT_REGULAR));
        return $validator->getErrorMask();
    }
    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function check($value, $schema)
    {
        return $this->validate($value, $schema);
    }
    /**
     * Alias to validate(), to maintain backwards-compatibility with the previous API
     */
    public function coerce(&$value, $schema)
    {
        return $this->validate($value, $schema, \RectorPrefix20210522\JsonSchema\Constraints\Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
