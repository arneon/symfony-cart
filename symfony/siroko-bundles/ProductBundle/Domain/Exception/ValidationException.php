<?php

namespace ProductBundle\Domain\Exception;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(private array $errors)
    {
        parent::__construct("Validation error(s) occurred: ".implode(", ", $errors));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
