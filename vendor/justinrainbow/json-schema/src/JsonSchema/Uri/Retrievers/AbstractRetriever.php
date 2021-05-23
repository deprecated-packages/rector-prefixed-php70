<?php

/**
 * JsonSchema
 *
 * @filesource
 */
namespace RectorPrefix20210523\JsonSchema\Uri\Retrievers;

/**
 * AbstractRetriever implements the default shared behavior
 * that all descendant Retrievers should inherit
 *
 * @author Steven Garcia <webwhammy@gmail.com>
 */
abstract class AbstractRetriever implements \RectorPrefix20210523\JsonSchema\Uri\Retrievers\UriRetrieverInterface
{
    /**
     * Media content type
     *
     * @var string
     */
    protected $contentType;
    /**
     * {@inheritdoc}
     *
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::getContentType()
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
