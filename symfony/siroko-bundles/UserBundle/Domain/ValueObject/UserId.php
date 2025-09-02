<?php

namespace UserBundle\Domain\ValueObject;

class UserId
{
    public function __construct(private readonly int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('UserId must be > 0.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
