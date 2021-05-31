<?php

namespace RectorPrefix20210531\JsonSchema;

use RectorPrefix20210531\JsonSchema\Constraints\BaseConstraint;
use RectorPrefix20210531\JsonSchema\Entity\JsonPointer;
use RectorPrefix20210531\JsonSchema\Exception\UnresolvableJsonPointerException;
use RectorPrefix20210531\JsonSchema\Uri\UriResolver;
use RectorPrefix20210531\JsonSchema\Uri\UriRetriever;
class SchemaStorage implements \RectorPrefix20210531\JsonSchema\SchemaStorageInterface
{
    const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema/';
    protected $uriRetriever;
    protected $uriResolver;
    protected $schemas = array();
    public function __construct(\RectorPrefix20210531\JsonSchema\UriRetrieverInterface $uriRetriever = null, \RectorPrefix20210531\JsonSchema\UriResolverInterface $uriResolver = null)
    {
        $this->uriRetriever = $uriRetriever ?: new \RectorPrefix20210531\JsonSchema\Uri\UriRetriever();
        $this->uriResolver = $uriResolver ?: new \RectorPrefix20210531\JsonSchema\Uri\UriResolver();
    }
    /**
     * @return UriRetrieverInterface
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }
    /**
     * @return UriResolverInterface
     */
    public function getUriResolver()
    {
        return $this->uriResolver;
    }
    /**
     * {@inheritdoc}
     */
    public function addSchema($id, $schema = null)
    {
        if (\is_null($schema) && $id !== self::INTERNAL_PROVIDED_SCHEMA_URI) {
            // if the schema was user-provided to Validator and is still null, then assume this is
            // what the user intended, as there's no way for us to retrieve anything else. User-supplied
            // schemas do not have an associated URI when passed via Validator::validate().
            $schema = $this->uriRetriever->retrieve($id);
        }
        // cast array schemas to object
        if (\is_array($schema)) {
            $schema = \RectorPrefix20210531\JsonSchema\Constraints\BaseConstraint::arrayToObjectRecursive($schema);
        }
        // workaround for bug in draft-03 & draft-04 meta-schemas (id & $ref defined with incorrect format)
        // see https://github.com/json-schema-org/JSON-Schema-Test-Suite/issues/177#issuecomment-293051367
        if (\is_object($schema) && \property_exists($schema, 'id')) {
            if ($schema->id == 'http://json-schema.org/draft-04/schema#') {
                $schema->properties->id->format = 'uri-reference';
            } elseif ($schema->id == 'http://json-schema.org/draft-03/schema#') {
                $schema->properties->id->format = 'uri-reference';
                $schema->properties->{'$ref'}->format = 'uri-reference';
            }
        }
        // resolve references
        $this->expandRefs($schema, $id);
        $this->schemas[$id] = $schema;
    }
    /**
     * Recursively resolve all references against the provided base
     *
     * @param mixed  $schema
     * @param string $base
     */
    private function expandRefs(&$schema, $base = null)
    {
        if (!\is_object($schema)) {
            if (\is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $base);
                }
            }
            return;
        }
        if (\property_exists($schema, 'id') && \is_string($schema->id) && $base != $schema->id) {
            $base = $this->uriResolver->resolve($schema->id, $base);
        }
        if (\property_exists($schema, '$ref') && \is_string($schema->{'$ref'})) {
            $refPointer = new \RectorPrefix20210531\JsonSchema\Entity\JsonPointer($this->uriResolver->resolve($schema->{'$ref'}, $base));
            $schema->{'$ref'} = (string) $refPointer;
        }
        foreach ($schema as &$member) {
            $this->expandRefs($member, $base);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getSchema($id)
    {
        if (!\array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }
        return $this->schemas[$id];
    }
    /**
     * {@inheritdoc}
     */
    public function resolveRef($ref)
    {
        $jsonPointer = new \RectorPrefix20210531\JsonSchema\Entity\JsonPointer($ref);
        // resolve filename for pointer
        $fileName = $jsonPointer->getFilename();
        if (!\strlen($fileName)) {
            throw new \RectorPrefix20210531\JsonSchema\Exception\UnresolvableJsonPointerException(\sprintf("Could not resolve fragment '%s': no file is defined", $jsonPointer->getPropertyPathAsString()));
        }
        // get & process the schema
        $refSchema = $this->getSchema($fileName);
        foreach ($jsonPointer->getPropertyPaths() as $path) {
            if (\is_object($refSchema) && \property_exists($refSchema, $path)) {
                $refSchema = $this->resolveRefSchema($refSchema->{$path});
            } elseif (\is_array($refSchema) && \array_key_exists($path, $refSchema)) {
                $refSchema = $this->resolveRefSchema($refSchema[$path]);
            } else {
                throw new \RectorPrefix20210531\JsonSchema\Exception\UnresolvableJsonPointerException(\sprintf('File: %s is found, but could not resolve fragment: %s', $jsonPointer->getFilename(), $jsonPointer->getPropertyPathAsString()));
            }
        }
        return $refSchema;
    }
    /**
     * {@inheritdoc}
     */
    public function resolveRefSchema($refSchema)
    {
        if (\is_object($refSchema) && \property_exists($refSchema, '$ref') && \is_string($refSchema->{'$ref'})) {
            $newSchema = $this->resolveRef($refSchema->{'$ref'});
            $refSchema = (object) (\get_object_vars($refSchema) + \get_object_vars($newSchema));
            unset($refSchema->{'$ref'});
        }
        return $refSchema;
    }
}
