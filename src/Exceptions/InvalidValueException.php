<?php
namespace SynergiTech\ChromePDF\Exceptions;

class InvalidValueException extends \InvalidArgumentException
{
    public function __construct(string $optionname, string $optionvalue)
    {
        parent::__construct(sprintf('Invalid value "%s" for option "%s"', $optionvalue, $optionname));
    }
}
