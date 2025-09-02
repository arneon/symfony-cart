<?php

namespace UserBundle\Domain\ValueObject;

class UserEmail
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("User email is required.");
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
