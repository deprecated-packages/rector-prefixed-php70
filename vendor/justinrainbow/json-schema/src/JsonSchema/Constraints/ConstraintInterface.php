<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210525\JsonSchema\Constraints;

use RectorPrefix20210525\JsonSchema\Entity\JsonPointer;
/**
 * The Constraints Interface
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
interface ConstraintInterface
{
    /**
     * returns all collected errors
     *
     * @return array
     */
    public function getErrors();
    /**
     * adds errors to this validator
     *
     * @param array $errors
     */
    public function addErrors(array $errors);
    /**
     * adds an error
     *
     * @param JsonPointer|null $path
     * @param string           $message
     * @param string           $constraint the constraint/rule that is broken, e.g.: 'minLength'
     * @param array            $more       more array elements to add to the error
     */
    public function addError(\RectorPrefix20210525\JsonSchema\Entity\JsonPointer $path = null, $message, $constraint = '', array $more = null);
    /**
     * checks if the validator has not raised errors
     *
     * @return bool
     */
    public function isValid();
    /**
     * invokes the validation of an element
     *
     * @abstract
     *
     * @param mixed            $value
     * @param mixed            $schema
     * @param JsonPointer|null $path
     * @param mixed            $i
     *
     * @throws \JsonSchema\Exception\ExceptionInterface
     */
    public function check(&$value, $schema = null, \RectorPrefix20210525\JsonSchema\Entity\JsonPointer $path = null, $i = null);
}
