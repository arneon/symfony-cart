<?php

namespace UserBundle\Domain\ValueObject;

class UserPassword
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("User password is required.");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }
}
