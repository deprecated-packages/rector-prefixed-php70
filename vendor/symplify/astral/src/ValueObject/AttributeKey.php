<?php

declare (strict_types=1);
namespace RectorPrefix20210616\Symplify\Astral\ValueObject;

final class AttributeKey
{
    /**
     * Convention key name in php-parser and PHPStan for parent node
     *
     * @var string
     */
    const PARENT = 'parent';
    /**
     * Used in php-paser, do not change
     *
     * @var string
     */
    const KIND = 'kind';
    /**
     * @api
     * @var string
     */
    const REFERENCED_CLASSES = 'referenced_classes';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    const PREVIOUS = 'previous';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    const NEXT = 'next';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    const STATEMENT_DEPTH = 'statementDepth';
    /**
     * Used by php-parser, do not change
     *
     * @var string
     */
    const COMMENTS = 'comments';
    /**
     * @var string
     */
    const REFERENCED_CLASS_CONSTANTS = 'referenced_class_constants';
    /**
     * @var string
     */
    const REFERENCED_METHOD_CALLS = 'referenced_method_calls';
}
