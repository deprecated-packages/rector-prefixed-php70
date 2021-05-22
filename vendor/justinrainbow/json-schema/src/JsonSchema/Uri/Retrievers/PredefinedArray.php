<?php

namespace RectorPrefix20210522\JsonSchema\Uri\Retrievers;

use RectorPrefix20210522\JsonSchema\Validator;
/**
 * URI retrieved based on a predefined array of schemas
 *
 * @example
 *
 *      $retriever = new PredefinedArray(array(
 *          'http://acme.com/schemas/person#'  => '{ ... }',
 *          'http://acme.com/schemas/address#' => '{ ... }',
 *      ))
 *
 *      $schema = $retriever->retrieve('http://acme.com/schemas/person#');
 */
class PredefinedArray extends \RectorPrefix20210522\JsonSchema\Uri\Retrievers\AbstractRetriever
{
    /**
     * Contains schemas as URI => JSON
     *
     * @var array
     */
    private $schemas;
    /**
     * Constructor
     *
     * @param array  $schemas
     * @param string $contentType
     */
    public function __construct(array $schemas, $contentType = \RectorPrefix20210522\JsonSchema\Validator::SCHEMA_MEDIA_TYPE)
    {
        $this->schemas = $schemas;
        $this->contentType = $contentType;
    }
    /**
     * {@inheritdoc}
     *
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::retrieve()
     */
    public function retrieve($uri)
    {
        if (!\array_key_exists($uri, $this->schemas)) {
            throw new \RectorPrefix20210522\JsonSchema\Exception\ResourceNotFoundException(\sprintf('The JSON schema "%s" was not found.', $uri));
        }
        return $this->schemas[$uri];
    }
}
