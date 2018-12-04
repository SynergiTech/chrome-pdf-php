<?php
namespace SynergiTech\ChromePDF\Exceptions;

class InvalidOptionException extends \InvalidArgumentException
{
    public function __construct(string $optionname)
    {
        parent::__construct(sprintf('The option "%s" does not exist', $optionname));
    }
}
