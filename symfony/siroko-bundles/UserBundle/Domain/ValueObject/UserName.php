<?php

namespace UserBundle\Domain\ValueObject;

class UserName
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("User name is required.");
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
