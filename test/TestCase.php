<?php

namespace SynergiTech\ChromePDF\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    public function hasKeyValue($key, $constraint = null)
    {
        return new Constraint\ArrayHasKeyValue($key, $constraint);
    }
}
