<?php

namespace SynergiTech\ChromePDF\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use SynergiTech\ChromePDF\Chrome;

class TestCase extends PHPUnitTestCase
{
    public function hasKeyValue($key, $constraint = null)
    {
        return new Constraint\ArrayHasKeyValue($key, $constraint);
    }

    protected function getMockedClient()
    {
        return $this->getMockBuilder(Chrome::class)
            ->addMethods(['post'])
            ->getMock();
    }
}
