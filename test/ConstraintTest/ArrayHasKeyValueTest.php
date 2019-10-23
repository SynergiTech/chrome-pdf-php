<?php

namespace SynergiTech\ChromePDF\Test\ConstraintTest;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use SynergiTech\ChromePDF\Test\Constraint\ArrayHasKeyValue;

class ArrayHasKeyValueTest extends TestCase
{
    public function test_missingKeyLast()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegexp('/Failed asserting that an array contains missing->key/');

        $c = new ArrayHasKeyValue(['missing', 'key']);
        $c->evaluate(['missing' => []]);
    }

    public function test_missingKey()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegexp('/Failed asserting that an array contains missing->key/');

        $c = new ArrayHasKeyValue(['missing', 'key', 'test']);
        $c->evaluate([ 'missing' => [] ]);
    }

    public function test_keyNotArray()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegexp('/Failed asserting that an array contains missing->key/');

        $c = new ArrayHasKeyValue(['missing', 'key', 'test']);
        $c->evaluate([ 'missing' => ['key' => 'test'] ]);
    }

    public function test_constraintFailed()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegexp("/Failed asserting that an array contains key and the value contains 'b'/");

        $c = new ArrayHasKeyValue(['key'], $this->contains('b'));
        $c->evaluate([
            'key' => ['a', 'c'],
            '',
            true
        ]);
    }

    public function test_keyExists()
    {
        $c = new ArrayHasKeyValue(['one', 'two', 'three']);
        $c->evaluate([
            'one' => [
                'two' => [
                    'three' => 'success'
                ]
            ]
        ]);
        $this->assertIsString('An exception was not thrown');
    }

    public function test_keyExistsAndPassesConstraint()
    {
        $c = new ArrayHasKeyValue(['one', 'two', 'three'], $this->identicalTo('success'));
        $c->evaluate([
            'one' => [
                'two' => [
                    'three' => 'success'
                ]
            ]
        ]);
        $this->assertIsString('An exception was not thrown');
    }
}
