<?php

namespace UserBundle\Domain\Exception;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(private array $errors, private int $errorCode = 400)
    {
        parent::__construct("Validation error(s) occurred: ".implode(", ", $errors), $errorCode );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
