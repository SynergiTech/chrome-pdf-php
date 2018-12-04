<?php
namespace SynergiTech\ChromePDF\Exceptions;

class CannotOverwriteFileException extends \RuntimeException
{
    public function __construct(string $filename)
    {
        parent::__construct(sprintf('File "%s" already exists and this function does not have permission to overwrite it from the arguments', $filename));
    }
}
