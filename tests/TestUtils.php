<?php

namespace Bencodex\Tests;

use PHPUnit\Framework\TestCase;

const OldExceptionConstraintName = 'PHPUnit_Framework_Constraint_Exception';
const NewExceptionConstraintName = 'PHPUnit\\Framework\\Constraint\\Exception';

trait TestUtils
{
    /**
     * Asserts the given code block throws the specified exception.
     * @param string $exceptionName The name of the exception which is expected
     *                              to be thrown.
     * @param callable $callback The code block expected to throw an exception.
     */
    public static function assertThrows($exceptionName, callable $callback)
    {
        if (!is_string($exceptionName)) {
            throw new \TypeError('The exceptionName must be a string.');
        }

        $reflection = new \ReflectionClass(
            class_exists(NewExceptionConstraintName)
                ? NewExceptionConstraintName
                : OldExceptionConstraintName
        );
        $constraint = $reflection->newInstance($exceptionName);

        try {
            $callback();
        } catch (\Error $e) {
            TestCase::assertThat($e, $constraint);
            return;
        } catch (\Exception $e) {
            TestCase::assertThat($e, $constraint);
            return;
        }

        TestCase::assertThat(null, $constraint);
    }
}
