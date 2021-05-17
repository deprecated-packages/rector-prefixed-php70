<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

final class MethodName
{
    /**
     * @var string
     */
    const CONSTRUCT = '__construct';
    /**
     * @var string
     */
    const DESCTRUCT = '__destruct';
    /**
     * @var string
     */
    const CLONE = '__clone';
    /**
     * Mostly used in unit tests
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#more-setup-than-teardown
     * @var string
     */
    const SET_UP = 'setUp';
    /**
     * Mostly used in unit tests
     * @var string
     */
    const TEAR_DOWN = 'tearDown';
    /**
     * @var string
     */
    const SET_STATE = '__set_state';
    /**
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#fixtures-sharing-fixture-examples-databasetest-php
     * @var string
     */
    const SET_UP_BEFORE_CLASS = 'setUpBeforeClass';
}
