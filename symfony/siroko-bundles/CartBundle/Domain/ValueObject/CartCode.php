<?php

namespace CartBundle\Domain\ValueObject;
use CartBundle\Domain\Exception\ValidationException;
use Ramsey\Uuid\Uuid;

class CartCode
{
    private string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid4()->toString();

        if (empty($this->value)) {
            throw new ValidationException(["Cart code is required."]);
        }

        if (strlen($this->value) > 36) {
            throw new ValidationException(["CartCode cannot be longer than 36 characters."]);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
